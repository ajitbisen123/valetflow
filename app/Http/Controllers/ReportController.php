<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ParkingTicket;
use App\Models\RetrievalRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $hotelId = Auth::user()->hotel_id;

        $startDateStr = $request->input('start_date', now()->subDays(7)->toDateString());
        $endDateStr = $request->input('end_date', now()->toDateString());

        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();

        $ticketsInRange = ParkingTicket::where('hotel_id', $hotelId)
            ->whereBetween('checked_in_at', [$startDate, $endDate])
            ->get();

        $totalCheckedIn = $ticketsInRange->count();

        // Average parking duration (minutes)
        $parkedTickets = $ticketsInRange->filter(fn ($t) => $t->parked_at !== null);
        $avgParkingMinutes = 0;
        if ($parkedTickets->isNotEmpty()) {
            $totalMinutes = $parkedTickets->sum(fn ($t) => $t->checked_in_at->diffInMinutes($t->parked_at));
            $avgParkingMinutes = round($totalMinutes / $parkedTickets->count(), 1);
        }

        // Retrieval performance
        $retrievalRequests = RetrievalRequest::where('hotel_id', $hotelId)
            ->whereBetween('requested_at', [$startDate, $endDate])
            ->whereNotNull('ready_at')
            ->get();

        $avgRetrievalMinutes = 0;
        $targetMetRate = 100;
        if ($retrievalRequests->isNotEmpty()) {
            $totalRetMinutes = $retrievalRequests->sum(fn ($r) => $r->requested_at->diffInMinutes($r->ready_at));
            $avgRetrievalMinutes = round($totalRetMinutes / $retrievalRequests->count(), 1);

            $onTimeCount = $retrievalRequests->filter(fn ($r) => $r->ready_at->lte($r->target_ready_at))->count();
            $targetMetRate = round(($onTimeCount / $retrievalRequests->count()) * 100, 1);
        }

        // Driver Workload
        $drivers = User::where('hotel_id', $hotelId)
            ->where('role', UserRole::Driver)
            ->withCount(['assignedTickets' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('checked_in_at', [$startDate, $endDate]);
            }])
            ->get();

        return view('admin.reports.index', [
            'startDate' => $startDateStr,
            'endDate' => $endDateStr,
            'metrics' => [
                'total_checked_in' => $totalCheckedIn,
                'avg_parking_minutes' => $avgParkingMinutes,
                'avg_retrieval_minutes' => $avgRetrievalMinutes,
                'target_met_rate' => $targetMetRate,
                'total_retrievals' => $retrievalRequests->count(),
            ],
            'drivers' => $drivers,
        ]);
    }
}
