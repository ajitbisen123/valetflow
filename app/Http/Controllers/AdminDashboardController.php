<?php

namespace App\Http\Controllers;

use App\Enums\RetrievalStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\ParkingTicket;
use App\Models\RetrievalRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $hotelId = Auth::user()->hotel_id;

        $todayStart = now()->startOfDay();

        $checkedInToday = ParkingTicket::where('hotel_id', $hotelId)
            ->where('checked_in_at', '>=', $todayStart)
            ->count();

        $parkingInProgress = ParkingTicket::where('hotel_id', $hotelId)
            ->where('status', TicketStatus::ParkingInProgress)
            ->count();

        $currentlyParked = ParkingTicket::where('hotel_id', $hotelId)
            ->where('status', TicketStatus::Parked)
            ->count();

        $retrievalRequested = ParkingTicket::where('hotel_id', $hotelId)
            ->where('status', TicketStatus::RetrievalRequested)
            ->count();

        $retrievalInProgress = ParkingTicket::where('hotel_id', $hotelId)
            ->where('status', TicketStatus::RetrievalInProgress)
            ->count();

        $readyAtGate = ParkingTicket::where('hotel_id', $hotelId)
            ->where('status', TicketStatus::ReadyAtGate)
            ->count();

        $overdueCount = RetrievalRequest::where('hotel_id', $hotelId)
            ->whereIn('status', [RetrievalStatus::Requested, RetrievalStatus::Assigned, RetrievalStatus::InProgress])
            ->where('target_ready_at', '<', now())
            ->count();

        $activeDrivers = User::where('hotel_id', $hotelId)
            ->where('role', UserRole::Driver)
            ->where('active', true)
            ->count();

        // Recent live queue tickets
        $liveTickets = ParkingTicket::with(['vehicle', 'assignedDriver', 'parkingLocation', 'activeRetrievalRequest'])
            ->where('hotel_id', $hotelId)
            ->whereNotIn('status', [TicketStatus::HandedOver, TicketStatus::Cancelled])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'counts' => [
                'checked_in_today' => $checkedInToday,
                'parking_in_progress' => $parkingInProgress,
                'currently_parked' => $currentlyParked,
                'retrieval_requested' => $retrievalRequested,
                'retrieval_in_progress' => $retrievalInProgress,
                'ready_at_gate' => $readyAtGate,
                'overdue' => $overdueCount,
                'active_drivers' => $activeDrivers,
            ],
            'liveTickets' => $liveTickets,
        ]);
    }
}

