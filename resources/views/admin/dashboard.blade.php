@extends('layouts.app')

@section('title', 'Operations Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Header & Quick Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Operations Dashboard</h1>
            <p class="text-xs text-slate-500 font-medium">Live valet monitoring and vehicle movement queue</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tickets.create') }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs tracking-wider uppercase transition shadow flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Check-In
            </a>
            <a href="{{ route('admin.tickets.index') }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition">
                <i class="fa-solid fa-list"></i> All Tickets
            </a>
        </div>
    </div>

    <!-- Live Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Card 1: Checked In -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                <span>CHECKED IN TODAY</span>
                <i class="fa-solid fa-car text-blue-500"></i>
            </div>
            <div class="text-3xl font-black text-slate-900">{{ $counts['checked_in_today'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Vehicles received at reception</div>
        </div>

        <!-- Card 2: Currently Parked -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                <span>CURRENTLY PARKED</span>
                <i class="fa-solid fa-square-parking text-emerald-500"></i>
            </div>
            <div class="text-3xl font-black text-emerald-600">{{ $counts['currently_parked'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">{{ $counts['parking_in_progress'] }} currently in transit to bays</div>
        </div>

        <!-- Card 3: Retrieval Active -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                <span>RETRIEVAL ACTIVE</span>
                <i class="fa-solid fa-bell text-purple-500"></i>
            </div>
            <div class="text-3xl font-black text-purple-600">{{ $counts['retrieval_requested'] + $counts['retrieval_in_progress'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">
                {{ $counts['retrieval_requested'] }} requested &bull; {{ $counts['retrieval_in_progress'] }} fetching
            </div>
        </div>

        <!-- Card 4: Ready at Gate / Overdue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                <span>READY AT GATE</span>
                <i class="fa-solid fa-flag-checkered text-teal-500"></i>
            </div>
            <div class="flex items-baseline gap-3">
                <span class="text-3xl font-black text-teal-600">{{ $counts['ready_at_gate'] }}</span>
                @if($counts['overdue'] > 0)
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation"></i> {{ $counts['overdue'] }} Overdue
                    </span>
                @endif
            </div>
            <div class="text-[11px] text-slate-500 mt-1">Awaiting guest key handover</div>
        </div>

    </div>

    <!-- Live Vehicle Movement Queue -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
            <div>
                <h2 class="font-bold text-slate-900 text-sm">Active Vehicle Operations Queue</h2>
                <p class="text-xs text-slate-500">Vehicles currently checked in, parked, or in retrieval</p>
            </div>
            <span class="text-xs text-slate-500 font-mono flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Auto-updating
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-600 font-semibold uppercase tracking-wider text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Ticket #</th>
                        <th class="py-3 px-4">Vehicle Details</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Assigned Driver</th>
                        <th class="py-3 px-4">Parking Location</th>
                        <th class="py-3 px-4">Time in Stage</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($liveTickets as $ticket)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="text-blue-600 hover:underline">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $ticket->vehicle->registration_number }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $ticket->vehicle->color }} {{ $ticket->vehicle->make }} {{ $ticket->vehicle->model }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $ticket->status->badgeClasses() }}">
                                    {{ $ticket->status->label() }}
                                </span>
                                @if($ticket->activeRetrievalRequest && $ticket->activeRetrievalRequest->isOverdue())
                                    <span class="ml-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-600 text-white animate-pulse">
                                        OVERDUE
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">
                                @if($ticket->assignedDriver)
                                    <span class="inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-id-badge text-amber-500"></i>
                                        {{ $ticket->assignedDriver->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $ticket->parkingLocation ? $ticket->parkingLocation->formatted_location : 'Not allocated yet' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 font-mono text-[11px]">
                                {{ $ticket->updated_at->diffForHumans() }}
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100" title="View Ticket Detail">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                                <a href="{{ route('admin.tickets.print-qr', $ticket->id) }}" class="p-1.5 rounded-lg text-amber-600 hover:text-amber-800 hover:bg-amber-50" title="Print QR Pass">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-circle-check text-2xl text-emerald-400 mb-2 block"></i>
                                No active tickets in operations queue. All vehicles are checked out or idle.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

