<?php

namespace App\Services;

use App\Enums\RetrievalStatus;
use App\Enums\TicketStatus;
use App\Models\AuditLog;
use App\Models\ParkingTicket;
use App\Models\QrToken;
use App\Models\RetrievalRequest;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RetrievalService
{
    public function __construct(
        protected TicketWorkflowService $workflowService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Create a guest retrieval request.
     */
    public function createGuestRequest(
        ParkingTicket $ticket,
        int $requestedMinutes,
        ?QrToken $guestToken = null,
        ?string $idempotencyKey = null
    ): RetrievalRequest {
        if (! in_array($requestedMinutes, [5, 10, 15], true)) {
            throw new InvalidArgumentException('Target ready time must be 5, 10, or 15 minutes.');
        }

        if ($idempotencyKey) {
            $existing = RetrievalRequest::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($ticket, $requestedMinutes, $guestToken, $idempotencyKey) {
            // Lock ticket to prevent race conditions
            $lockedTicket = ParkingTicket::where('id', $ticket->id)->lockForUpdate()->firstOrFail();

            if ($lockedTicket->status !== TicketStatus::Parked) {
                throw new DomainException('Vehicle must be in PARKED state to request retrieval.');
            }

            // Check if there is already an active retrieval request
            $activeRequest = RetrievalRequest::where('ticket_id', $lockedTicket->id)
                ->whereIn('status', [
                    RetrievalStatus::Requested->value,
                    RetrievalStatus::Assigned->value,
                    RetrievalStatus::InProgress->value,
                    RetrievalStatus::ReadyAtGate->value,
                    RetrievalStatus::Delayed->value,
                ])
                ->lockForUpdate()
                ->first();

            if ($activeRequest) {
                return $activeRequest;
            }

            $now = now();
            $targetReadyAt = (clone $now)->addMinutes($requestedMinutes);

            $request = RetrievalRequest::create([
                'hotel_id' => $lockedTicket->hotel_id,
                'ticket_id' => $lockedTicket->id,
                'requested_by_guest_token_id' => $guestToken?->id,
                'requested_minutes' => $requestedMinutes,
                'requested_at' => $now,
                'target_ready_at' => $targetReadyAt,
                'status' => RetrievalStatus::Requested,
                'idempotency_key' => $idempotencyKey,
            ]);

            // Transition ticket to RETRIEVAL_REQUESTED
            $this->workflowService->transition(
                $lockedTicket,
                TicketStatus::RetrievalRequested,
                actor: null,
                reason: "Guest requested retrieval in {$requestedMinutes} minutes"
            );

            // Audit
            AuditLog::create([
                'hotel_id' => $lockedTicket->hotel_id,
                'actor_user_id' => null,
                'action' => 'retrieval.requested',
                'subject_type' => RetrievalRequest::class,
                'subject_id' => $request->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'requested_minutes' => $requestedMinutes,
                    'target_ready_at' => $targetReadyAt->toIso8601String(),
                ],
                'created_at' => $now,
            ]);

            // Notify staff
            $this->notificationService->send(
                hotelId: $lockedTicket->hotel_id,
                messageType: 'retrieval_requested',
                payload: [
                    'ticket_number' => $lockedTicket->ticket_number,
                    'target_ready_at' => $targetReadyAt->toIso8601String(),
                    'requested_minutes' => $requestedMinutes,
                ],
                requestId: $request->id
            );

            return $request;
        });
    }

    /**
     * Assign driver to retrieval request.
     */
    public function assignDriver(RetrievalRequest $request, User $driver, ?User $assignedBy = null): RetrievalRequest
    {
        return DB::transaction(function () use ($request, $driver, $assignedBy) {
            $request->update([
                'assigned_driver_id' => $driver->id,
                'status' => RetrievalStatus::Assigned,
            ]);

            $request->ticket->update(['assigned_driver_id' => $driver->id]);

            AuditLog::create([
                'hotel_id' => $request->hotel_id,
                'actor_user_id' => $assignedBy?->id,
                'action' => 'retrieval.driver_assigned',
                'subject_type' => RetrievalRequest::class,
                'subject_id' => $request->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => ['driver_id' => $driver->id, 'driver_name' => $driver->name],
                'created_at' => now(),
            ]);

            return $request->fresh();
        });
    }

    /**
     * Driver starts retrieval.
     */
    public function startRetrieval(RetrievalRequest $request, User $driver): RetrievalRequest
    {
        return DB::transaction(function () use ($request, $driver) {
            $now = now();
            $request->update([
                'assigned_driver_id' => $driver->id,
                'started_at' => $now,
                'accepted_at' => $request->accepted_at ?? $now,
                'status' => RetrievalStatus::InProgress,
            ]);

            $request->ticket->update(['assigned_driver_id' => $driver->id]);

            $this->workflowService->transition(
                $request->ticket,
                TicketStatus::RetrievalInProgress,
                $driver,
                'Driver began vehicle retrieval'
            );

            return $request->fresh();
        });
    }

    /**
     * Mark vehicle Ready at Gate.
     */
    public function markReadyAtGate(RetrievalRequest $request, User $driver): RetrievalRequest
    {
        return DB::transaction(function () use ($request, $driver) {
            $now = now();
            $request->update([
                'ready_at' => $now,
                'status' => RetrievalStatus::ReadyAtGate,
            ]);

            $this->workflowService->transition(
                $request->ticket,
                TicketStatus::ReadyAtGate,
                $driver,
                'Vehicle is physically ready at the gate collection point'
            );

            return $request->fresh();
        });
    }

    /**
     * Complete handover and close request and ticket.
     */
    public function completeHandover(RetrievalRequest $request, User $actor, ?string $notes = null): RetrievalRequest
    {
        return DB::transaction(function () use ($request, $actor, $notes) {
            $now = now();
            $request->update([
                'completed_at' => $now,
                'status' => RetrievalStatus::Completed,
            ]);

            $this->workflowService->completeHandover($request->ticket, $actor, $notes);

            return $request->fresh();
        });
    }
}

