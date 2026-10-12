<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $hotelId = Auth::user()->hotel_id;

        $query = AuditLog::with('actor')
            ->where('hotel_id', $hotelId);

        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($actor = $request->input('actor')) {
            $query->whereHas('actor', function ($q) use ($actor) {
                $q->where('name', 'like', "%{$actor}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'action' => $action,
            'actor' => $actor,
        ]);
    }
}

