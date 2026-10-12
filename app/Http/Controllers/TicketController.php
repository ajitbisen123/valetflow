<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\GuestSession;
use App\Models\ParkingLocation;
use App\Models\ParkingTicket;
use App\Models\TicketEvent;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\DriverAssignmentService;
use App\Services\QrTokenService;
use App\Services\TicketWorkflowService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(
        protected QrTokenService $qrTokenService,
        protected TicketWorkflowService $workflowService,
        protected DriverAssignmentService $assignmentService
    ) {}

    public function index(Request $request): View
    {
        $hotelId = Auth::user()->hotel_id;
        $query = ParkingTicket::with(['vehicle', 'assignedDriver', 'parkingLocation', 'activeRetrievalRequest'])
            ->where('hotel_id', $hotelId);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', function ($vq) use ($search) {
                        $vq->where('registration_number', 'like', "%{$search}%")
                            ->orWhere('make', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    })
                    ->orWhereHas('guestSession', function ($gq) use ($search) {
                        $gq->where('guest_name', 'like', "%{$search}%")
                            ->orWhere('booking_reference', 'like', "%{$search}%");
                    });
            });
        }

        if ($driverId = $request->input('driver_id')) {
            $query->where('assigned_driver_id', $driverId);
        }

        $tickets = $query->latest('checked_in_at')->paginate(20)->withQueryString();

        $drivers = User::where('hotel_id', $hotelId)
            ->where('role', UserRole::Driver)
            ->where('active', true)
            ->get();

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'drivers' => $drivers,
            'currentStatus' => $status,
            'search' => $search,
            'currentDriver' => $driverId,
        ]);
    }

    public function create(): View
    {
        $hotelId = Auth::user()->hotel_id;
        $drivers = $this->assignmentService->getActiveDrivers($hotelId);

        return view('admin.tickets.create', [
            'drivers' => $drivers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registration_number' => ['required', 'string', 'max:20'],
            'make' => ['nullable', 'string', 'max:50'],
            'model' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:30'],
            'guest_name' => ['nullable', 'string', 'max:100'],
            'booking_reference' => ['nullable', 'string', 'max:50'],
            'notes_private' => ['nullable', 'string', 'max:500'],
            'assigned_driver_id' => ['nullable', 'exists:users,id'],
        ]);

        $user = Auth::user();
        $hotelId = $user->hotel_id;

        $ticket = DB::transaction(function () use ($validated, $user, $hotelId) {
            // Normalize registration number
            $regNumber = strtoupper(trim($validated['registration_number']));

            // Vehicle
            $vehicle = Vehicle::create([
                'hotel_id' => $hotelId,
                'registration_number' => $regNumber,
                'make' => $validated['make'] ?? null,
                'model' => $validated['model'] ?? null,
                'color' => $validated['color'] ?? null,
                'notes_private' => $validated['notes_private'] ?? null,
            ]);

            // Guest session
            $guestSession = null;
            if (! empty($validated['guest_name']) || ! empty($validated['booking_reference'])) {
                $guestSession = GuestSession::create([
                    'hotel_id' => $hotelId,
                    'guest_name' => $validated['guest_name'] ?? null,
                    'booking_reference' => $validated['booking_reference'] ?? null,
                    'created_by' => $user->id,
                ]);
            }

            // Generate unique human-readable ticket number (e.g. VF-5821)
            $count = ParkingTicket::where('hotel_id', $hotelId)->count() + 1001;
            $ticketNumber = 'VF-'.$count;
            while (ParkingTicket::where('hotel_id', $hotelId)->where('ticket_number', $ticketNumber)->exists()) {
                $count++;
                $ticketNumber = 'VF-'.$count;
            }

            $ticket = ParkingTicket::create([
                'hotel_id' => $hotelId,
                'ticket_number' => $ticketNumber,
                'guest_session_id' => $guestSession?->id,
                'vehicle_id' => $vehicle->id,
                'assigned_driver_id' => $validated['assigned_driver_id'] ?? null,
                'status' => TicketStatus::CheckedIn,
                'checked_in_at' => now(),
                'created_by' => $user->id,
            ]);

            // Generate QR token
            $this->qrTokenService->generateForTicket($ticket, $user);

            // Record Event
            TicketEvent::create([
                'hotel_id' => $hotelId,
                'ticket_id' => $ticket->id,
                'actor_user_id' => $user->id,
                'event_type' => 'check_in',
                'old_status' => null,
                'new_status' => TicketStatus::CheckedIn->value,
                'reason' => 'Ticket created at reception',
                'created_at' => now(),
            ]);

            // Audit Log
            AuditLog::create([
                'hotel_id' => $hotelId,
                'actor_user_id' => $user->id,
                'action' => 'ticket.created',
                'subject_type' => ParkingTicket::class,
                'subject_id' => $ticket->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => ['ticket_number' => $ticketNumber, 'reg' => $regNumber],
                'created_at' => now(),
            ]);

            return $ticket;
        });

        return redirect()->route('admin.tickets.print-qr', $ticket->id)
            ->with('success', "Ticket #{$ticket->ticket_number} created successfully! You can now print the guest QR card.");
    }

    public function show(ParkingTicket $ticket): View
    {
        $user = Auth::user();
        if ($ticket->hotel_id !== $user->hotel_id) {
            abort(403);
        }

        $drivers = $this->assignmentService->getActiveDrivers($user->hotel_id);
        $locations = ParkingLocation::where('hotel_id', $user->hotel_id)->where('active', true)->get();

        return view('admin.tickets.show', [
            'ticket' => $ticket->load(['vehicle', 'guestSession', 'assignedDriver', 'parkingLocation', 'events.actor', 'retrievalRequests.assignedDriver']),
            'drivers' => $drivers,
            'locations' => $locations,
            'activeToken' => $ticket->activeQrToken,
        ]);
    }

    public function assignDriver(Request $request, ParkingTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'exists:users,id'],
        ]);

        $user = Auth::user();
        $driver = User::findOrFail($validated['driver_id']);

        try {
            $this->assignmentService->assignDriverToTicket($ticket, $driver, $user);

            return back()->with('success', "Driver {$driver->name} assigned to ticket #{$ticket->ticket_number}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reissueQr(ParkingTicket $ticket): RedirectResponse
    {
        $user = Auth::user();
        if ($ticket->hotel_id !== $user->hotel_id) {
            abort(403);
        }

        // Revoke old tokens
        $this->qrTokenService->revokeTicketTokens($ticket);

        // Generate new token
        $result = $this->qrTokenService->generateForTicket($ticket, $user);

        AuditLog::create([
            'hotel_id' => $ticket->hotel_id,
            'actor_user_id' => $user->id,
            'action' => 'qr.reissued',
            'subject_type' => ParkingTicket::class,
            'subject_id' => $ticket->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.tickets.print-qr', $ticket->id)
            ->with('success', 'Previous QR token revoked and new QR card generated.');
    }

    public function printQr(ParkingTicket $ticket): View
    {
        $user = Auth::user();
        if ($ticket->hotel_id !== $user->hotel_id) {
            abort(403);
        }

        $activeToken = $ticket->activeQrToken;
        if (! $activeToken) {
            $gen = $this->qrTokenService->generateForTicket($ticket, $user);
            $rawToken = $gen['token'];
        } else {
            // For print rendering, we need a valid guest URL.
            // If already hashed and raw not stored, generate a fresh active token for print
            $gen = $this->qrTokenService->generateForTicket($ticket, $user);
            $rawToken = $gen['token'];
        }

        $guestUrl = route('guest.show', $rawToken);
        $qrSvg = $this->qrTokenService->generateSvg($guestUrl, 260);

        return view('admin.tickets.print-qr', [
            'ticket' => $ticket->load(['vehicle', 'hotel']),
            'guestUrl' => $guestUrl,
            'qrSvg' => $qrSvg,
        ]);
    }
}
