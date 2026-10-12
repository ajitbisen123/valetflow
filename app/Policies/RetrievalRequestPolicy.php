<?php

namespace App\Policies;

use App\Models\RetrievalRequest;
use App\Models\User;

class RetrievalRequestPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, RetrievalRequest $request): bool
    {
        return $user->hotel_id === $request->hotel_id;
    }

    public function assign(User $user, RetrievalRequest $request): bool
    {
        return $user->hotel_id === $request->hotel_id && $user->canDispatch();
    }

    public function operate(User $user, RetrievalRequest $request): bool
    {
        if ($user->hotel_id !== $request->hotel_id) {
            return false;
        }

        if ($user->isDriver()) {
            return $request->assigned_driver_id === $user->id;
        }

        return $user->isHotelAdmin() || $user->isManager();
    }
}

