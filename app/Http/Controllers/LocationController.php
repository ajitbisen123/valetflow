<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ParkingLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $hotelId = Auth::user()->hotel_id;

        $locations = ParkingLocation::where('hotel_id', $hotelId)
            ->withCount(['tickets' => function ($q) {
                $q->where('status', 'PARKED');
            }])
            ->orderBy('zone')
            ->orderBy('floor')
            ->orderBy('slot')
            ->get();

        return view('admin.locations.index', [
            'locations' => $locations,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'zone' => ['required', 'string', 'max:50'],
            'floor' => ['nullable', 'string', 'max:50'],
            'slot' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $currentUser = Auth::user();

        $location = ParkingLocation::create([
            'hotel_id' => $currentUser->hotel_id,
            'zone' => $validated['zone'],
            'floor' => $validated['floor'] ?? null,
            'slot' => $validated['slot'] ?? null,
            'description' => $validated['description'] ?? null,
            'active' => true,
        ]);

        AuditLog::create([
            'hotel_id' => $currentUser->hotel_id,
            'actor_user_id' => $currentUser->id,
            'action' => 'location.created',
            'subject_type' => ParkingLocation::class,
            'subject_id' => $location->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['zone' => $location->zone, 'slot' => $location->slot],
            'created_at' => now(),
        ]);

        return back()->with('success', "Parking bay '{$location->formatted_location}' added successfully.");
    }

    public function toggleActive(ParkingLocation $location): RedirectResponse
    {
        $currentUser = Auth::user();
        if ($location->hotel_id !== $currentUser->hotel_id) {
            abort(403);
        }

        $location->update(['active' => ! $location->active]);

        return back()->with('success', "Location {$location->formatted_location} status updated.");
    }
}

