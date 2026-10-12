<?php

namespace App\Policies;

use App\Models\ParkingTicket;
use App\Models\User;

class ParkingTicketPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function view(User $user, ParkingTicket $ticket): bool
    {
        return $user->hotel_id === $ticket->hotel_id;
    }

    public function create(User $user): bool
    {
        return $user->canCreateTicket();
    }

    public function update(User $user, ParkingTicket $ticket): bool
    {
        return $user->hotel_id === $ticket->hotel_id && ($user->isHotelAdmin() || $user->isManager());
    }

    public function assignDriver(User $user, ParkingTicket $ticket): bool
    {
        return $user->hotel_id === $ticket->hotel_id && $user->canDispatch();
    }

    public function park(User $user, ParkingTicket $ticket): bool
    {
        if ($user->hotel_id !== $ticket->hotel_id) {
            return false;
        }

        if ($user->isDriver()) {
            return $ticket->assigned_driver_id === $user->id;
        }

        return $user->isHotelAdmin() || $user->isManager();
    }

    public function handover(User $user, ParkingTicket $ticket): bool
    {
        return $user->hotel_id === $ticket->hotel_id;
    }
}

