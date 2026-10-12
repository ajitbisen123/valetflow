<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DriverManagementController extends Controller
{
    public function index(): View
    {
        $hotelId = Auth::user()->hotel_id;

        $drivers = User::where('hotel_id', $hotelId)
            ->where('role', UserRole::Driver)
            ->withCount(['assignedTickets' => function ($q) {
                $q->whereIn('status', ['CHECKED_IN', 'PARKING_IN_PROGRESS', 'RETRIEVAL_REQUESTED', 'RETRIEVAL_IN_PROGRESS']);
            }])
            ->withCount(['assignedTickets as completed_today_count' => function ($q) {
                $q->where('status', 'HANDED_OVER')->where('handed_over_at', '>=', now()->startOfDay());
            }])
            ->get();

        return view('admin.drivers.index', [
            'drivers' => $drivers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $currentUser = Auth::user();

        $driver = User::create([
            'hotel_id' => $currentUser->hotel_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::Driver,
            'active' => true,
        ]);

        AuditLog::create([
            'hotel_id' => $currentUser->hotel_id,
            'actor_user_id' => $currentUser->id,
            'action' => 'driver.created',
            'subject_type' => User::class,
            'subject_id' => $driver->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['driver_name' => $driver->name, 'email' => $driver->email],
            'created_at' => now(),
        ]);

        return back()->with('success', "Driver account for {$driver->name} created successfully.");
    }

    public function toggleActive(User $driver): RedirectResponse
    {
        $currentUser = Auth::user();
        if ($driver->hotel_id !== $currentUser->hotel_id) {
            abort(403);
        }

        $driver->update(['active' => ! $driver->active]);

        AuditLog::create([
            'hotel_id' => $currentUser->hotel_id,
            'actor_user_id' => $currentUser->id,
            'action' => $driver->active ? 'driver.activated' : 'driver.deactivated',
            'subject_type' => User::class,
            'subject_id' => $driver->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        $status = $driver->active ? 'activated' : 'deactivated';

        return back()->with('success', "Driver {$driver->name} has been {$status}.");
    }
}

