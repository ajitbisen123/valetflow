<?php

namespace App\Http\Controllers;

use App\Enums\RetrievalStatus;
use App\Enums\TicketStatus;
use App\Models\ParkingLocation;
use App\Models\ParkingTicket;
use App\Models\RetrievalRequest;
use App\Services\QrTokenService;
use App\Services\RetrievalService;
use App\Services\TicketWorkflowService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function __construct(
        protected TicketWorkflowService $workflowService,
        protected RetrievalService $retrievalService,
        protected QrTokenService $qrTokenService
    ) {}

    public function dashboard(): View
    {
        $driver = Auth::user();

        // Tasks assigned to this driver
        $assignedTickets = ParkingTicket::with(['vehicle', 'parkingLocation'])
            ->where('hotel_id', $driver->hotel_id)
            ->where('assigned_driver_id', $driver->id)
            ->whereIn('status', [TicketStatus::CheckedIn, TicketStatus::ParkingInProgress])
            ->latest('checked_in_at')
            ->get();

        $activeRetrievals = RetrievalRequest::with(['ticket.vehicle', 'ticket.parkingLocation'])
            ->where('hotel_id', $driver->hotel_id)
            ->where('assigned_driver_id', $driver->id)
            ->whereIn('status', [RetrievalStatus::Assigned, RetrievalStatus::InProgress, RetrievalStatus::ReadyAtGate])
            ->orderBy('target_ready_at', 'asc')
            ->get();

        // Unassigned requests available for pickup
        $unassignedRetrievals = RetrievalRequest::with(['ticket.vehicle', 'ticket.parkingLocation'])
            ->where('hotel_id', $driver->hotel_id)
            ->whereNull('assigned_driver_id')
            ->where('status', RetrievalStatus::Requested)
            ->orderBy('target_ready_at', 'asc')
            ->get();

        return view('driver.dashboard', [
            'driver' => $driver,
            'assignedTickets' => $assignedTickets,
            'activeRetrievals' => $activeRetrievals,
            'unassignedRetrievals' => $unassignedRetrievals,
        ]);
    }

    public function scan(): View
    {
        return view('driver.scan');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $input = trim($request->input('query', ''));
        $driver = Auth::user();

        if (empty($input)) {
            return back()->with('error', 'Please enter a ticket number or scan a valid QR code.');
        }

        // Check if input is a URL containing a guest token
        if (str_contains($input, '/guest/')) {
            $parts = explode('/guest/', $input);
            $token = end($parts);
            $qrToken = $this->qrTokenService->findValidGuestToken($token);
            if ($qrToken && $qrToken->ticket) {
                return redirect()->route('driver.tickets.show', $qrToken->ticket_id);
            }
        }

        // Check by ticket number
        $ticket = ParkingTicket::where('hotel_id', $driver->hotel_id)
            ->where('ticket_number', $input)
            ->first();

        if ($ticket) {
            return redirect()->route('driver.tickets.show', $ticket->id);
        }

        return back()->with('error', "No ticket found matching '{$input}' in your hotel.");
    }

    public function showTicket(ParkingTicket $ticket): View
    {
        $driver = Auth::user();
        if ($ticket->hotel_id !== $driver->hotel_id) {
            abort(403);
        }

        $locations = ParkingLocation::where('hotel_id', $driver->hotel_id)
            ->where('active', true)
            ->orderBy('zone')
            ->get();

        return view('driver.ticket-detail', [
            'ticket' => $ticket->load(['vehicle', 'parkingLocation', 'activeRetrievalRequest', 'events.actor']),
            'locations' => $locations,
            'driver' => $driver,
        ]);
    }

    public function startParking(ParkingTicket $ticket): RedirectResponse
    {
        $driver = Auth::user();
        try {
            $this->workflowService->startParking($ticket, $driver);

            return back()->with('success', 'Parking started. Proceed to locate an available parking bay.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markParked(Request $request, ParkingTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'parking_location_id' => ['required', 'exists:parking_locations,id'],
        ]);

        $driver = Auth::user();
        $location = ParkingLocation::findOrFail($validated['parking_location_id']);

        try {
            $this->workflowService->markParked($ticket, $location, $driver);

            return redirect()->route('driver.dashboard')->with('success', "Vehicle marked as parked at {$location->formatted_location}!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function acceptRequest(RetrievalRequest $request): RedirectResponse
    {
        $driver = Auth::user();
        try {
            $this->retrievalService->assignDriver($request, $driver, $driver);

            return redirect()->route('driver.dashboard')->with('success', 'Retrieval task accepted!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function startRetrieval(RetrievalRequest $request): RedirectResponse
    {
        $driver = Auth::user();
        try {
            $this->retrievalService->startRetrieval($request, $driver);

            return back()->with('success', 'Retrieval in progress. Locate and fetch vehicle.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function readyAtGate(RetrievalRequest $request): RedirectResponse
    {
        $driver = Auth::user();
        try {
            $this->retrievalService->markReadyAtGate($request, $driver);

            return back()->with('success', 'Vehicle marked Ready at Gate. Awaiting guest collection.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function completeHandover(Request $request, RetrievalRequest $retrievalRequest): RedirectResponse
    {
        $driver = Auth::user();
        $notes = $request->input('notes');

        try {
            $this->retrievalService->completeHandover($retrievalRequest, $driver, $notes);

            return redirect()->route('driver.dashboard')->with('success', 'Handover completed and parking ticket closed.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function history(): View
    {
        $driver = Auth::user();
        $history = ParkingTicket::with(['vehicle', 'parkingLocation'])
            ->where('hotel_id', $driver->hotel_id)
            ->where('assigned_driver_id', $driver->id)
            ->where('status', TicketStatus::HandedOver)
            ->latest('handed_over_at')
            ->paginate(15);

        return view('driver.history', [
            'history' => $history,
            'driver' => $driver,
        ]);
    }
}

