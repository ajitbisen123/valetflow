<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\AuditLog;
use App\Models\ParkingLocation;
use App\Models\ParkingTicket;
use App\Models\TicketEvent;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class TicketWorkflowService
{
    /**
     * Allowed transition map.
     *
     * @var array<string, list<string>>
     */
    protected array $allowedTransitions = [
        TicketStatus::CheckedIn->value => [
            TicketStatus::ParkingInProgress->value,
            TicketStatus::Cancelled->value,
        ],
        TicketStatus::ParkingInProgress->value => [
            TicketStatus::Parked->value,
            TicketStatus::ParkingIssue->value,
        ],
        TicketStatus::Parked->value => [
            TicketStatus::RetrievalRequested->value,
            TicketStatus::OnHold->value,
        ],
        TicketStatus::RetrievalRequested->value => [
            TicketStatus::RetrievalInProgress->value,
            TicketStatus::Parked->value,
            TicketStatus::OnHold->value,
        ],
        TicketStatus::RetrievalInProgress->value => [
            TicketStatus::ReadyAtGate->value,
            TicketStatus::OnHold->value,
        ],
        TicketStatus::ReadyAtGate->value => [
            TicketStatus::HandedOver->value,
            TicketStatus::RetrievalInProgress->value,
            TicketStatus::OnHold->value,
        ],
        TicketStatus::ParkingIssue->value => [
            TicketStatus::ParkingInProgress->value,
            TicketStatus::OnHold->value,
            TicketStatus::Cancelled->value,
        ],
        TicketStatus::OnHold->value => [
            TicketStatus::CheckedIn->value,
            TicketStatus::Parked->value,
            TicketStatus::RetrievalRequested->value,
            TicketStatus::RetrievalInProgress->value,
            TicketStatus::ReadyAtGate->value,
            TicketStatus::Cancelled->value,
        ],
        TicketStatus::HandedOver->value => [],
        TicketStatus::Cancelled->value => [],
    ];

    /**
     * Transition ticket to a new status.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function transition(
        ParkingTicket $ticket,
        TicketStatus $newStatus,
        ?User $actor = null,
        ?string $reason = null,
        array $metadata = []
    ): ParkingTicket {
        $currentStatusValue = $ticket->status->value;
        $newStatusValue = $newStatus->value;

        if ($currentStatusValue === $newStatusValue) {
            return $ticket;
        }

        $allowed = $this->allowedTransitions[$currentStatusValue] ?? [];
        if (! in_array($newStatusValue, $allowed, true)) {
            throw new DomainException("Cannot transition ticket #{$ticket->ticket_number} from {$ticket->status->label()} to {$newStatus->label()}.");
        }

        return DB::transaction(function () use ($ticket, $newStatus, $actor, $reason, $metadata, $currentStatusValue, $newStatusValue) {
            $now = now();
            $updates = ['status' => $newStatus];

            if ($newStatus === TicketStatus::Parked) {
                if (empty($ticket->parking_location_id) && empty($metadata['parking_location_id'])) {
                    throw new DomainException('A valid parking location is required before marking as parked.');
                }
                if (! empty($metadata['parking_location_id'])) {
                    $updates['parking_location_id'] = $metadata['parking_location_id'];
                }
                $updates['parked_at'] = $now;
            }

            if ($newStatus === TicketStatus::RetrievalRequested) {
                $updates['retrieval_requested_at'] = $now;
            }

            if ($newStatus === TicketStatus::ReadyAtGate) {
                $updates['ready_at_gate_at'] = $now;
            }

            if ($newStatus === TicketStatus::HandedOver) {
                $updates['handed_over_at'] = $now;
                $updates['closed_at'] = $now;
            }

            if ($newStatus === TicketStatus::Cancelled) {
                $updates['closed_at'] = $now;
            }

            $ticket->update($updates);

            // Record immutable event history
            TicketEvent::create([
                'hotel_id' => $ticket->hotel_id,
                'ticket_id' => $ticket->id,
                'actor_user_id' => $actor?->id,
                'event_type' => 'status_change',
                'old_status' => $currentStatusValue,
                'new_status' => $newStatusValue,
                'reason' => $reason,
                'metadata' => $metadata,
                'created_at' => $now,
            ]);

            // Record audit log
            AuditLog::create([
                'hotel_id' => $ticket->hotel_id,
                'actor_user_id' => $actor?->id,
                'action' => "ticket.status.{$newStatusValue}",
                'subject_type' => ParkingTicket::class,
                'subject_id' => $ticket->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'old_status' => $currentStatusValue,
                    'new_status' => $newStatusValue,
                    'reason' => $reason,
                ],
                'created_at' => $now,
            ]);

            return $ticket->fresh();
        });
    }

    /**
     * Start parking vehicle.
     */
    public function startParking(ParkingTicket $ticket, User $driver): ParkingTicket
    {
        return $this->transition(
            $ticket,
            TicketStatus::ParkingInProgress,
            $actor = $driver,
            $reason = 'Driver began parking vehicle'
        );
    }

    /**
     * Complete parking vehicle and record location.
     */
    public function markParked(ParkingTicket $ticket, ParkingLocation $location, User $driver): ParkingTicket
    {
        return $this->transition(
            $ticket,
            TicketStatus::Parked,
            $actor = $driver,
            $reason = 'Vehicle successfully parked',
            ['parking_location_id' => $location->id]
        );
    }

    /**
     * Complete handover and close ticket.
     */
    public function completeHandover(ParkingTicket $ticket, User $actor, ?string $notes = null): ParkingTicket
    {
        if ($ticket->status !== TicketStatus::ReadyAtGate) {
            throw new DomainException('Vehicle must be Ready at Gate before handover can be completed.');
        }

        $ticket = $this->transition(
            $ticket,
            TicketStatus::HandedOver,
            $actor,
            $reason = 'Vehicle handed over to guest: '.($notes ?: 'No disputes')
        );

        // Revoke active guest QR tokens upon handover
        app(QrTokenService::class)->revokeTicketTokens($ticket);

        return $ticket;
    }
}
