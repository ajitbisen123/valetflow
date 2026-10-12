<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\ParkingTicket;
use App\Models\User;
use DomainException;
use Illuminate\Support\Collection;

class DriverAssignmentService
{
    /**
     * Get active drivers for a hotel.
     *
     * @return Collection<int, User>
     */
    public function getActiveDrivers(string $hotelId): Collection
    {
        return User::where('hotel_id', $hotelId)
            ->where('role', UserRole::Driver)
            ->where('active', true)
            ->withCount(['assignedTickets' => function ($q) {
                $q->whereIn('status', ['CHECKED_IN', 'PARKING_IN_PROGRESS', 'RETRIEVAL_REQUESTED', 'RETRIEVAL_IN_PROGRESS']);
            }])
            ->get();
    }

    /**
     * Assign a driver to a parking ticket.
     */
    public function assignDriverToTicket(ParkingTicket $ticket, User $driver, ?User $actor = null): ParkingTicket
    {
        if ($driver->hotel_id !== $ticket->hotel_id) {
            throw new DomainException('Cannot assign a driver from a different hotel.');
        }

        $ticket->update(['assigned_driver_id' => $driver->id]);

        AuditLog::create([
            'hotel_id' => $ticket->hotel_id,
            'actor_user_id' => $actor?->id,
            'action' => 'ticket.driver_assigned',
            'subject_type' => ParkingTicket::class,
            'subject_id' => $ticket->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => ['driver_id' => $driver->id, 'driver_name' => $driver->name],
            'created_at' => now(),
        ]);

        return $ticket->fresh();
    }
}

