@extends('layouts.app')

@section('title', 'Ticket Registry')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Ticket Registry</h1>
            <p class="text-xs text-slate-500 font-medium">Search, filter and inspect all hotel parking tickets</p>
        </div>
        <a href="{{ route('admin.tickets.create') }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs tracking-wider uppercase transition shadow flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> New Check-In
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search ticket #, reg, guest..."
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
            </div>

            <div>
                <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                    <option value="">-- All Statuses --</option>
                    @foreach(\App\Enums\TicketStatus::cases() as $st)
                        <option value="{{ $st->value }}" {{ $currentStatus === $st->value ? 'selected' : '' }}>
                            {{ $st->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="driver_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                    <option value="">-- All Assigned Drivers --</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ $currentDriver == $driver->id ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if($search || $currentStatus || $currentDriver)
                    <a href="{{ route('admin.tickets.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs transition" title="Clear Filters">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-600 font-semibold uppercase tracking-wider text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Ticket #</th>
                        <th class="py-3 px-4">Vehicle</th>
                        <th class="py-3 px-4">Guest</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Location</th>
                        <th class="py-3 px-4">Assigned Driver</th>
                        <th class="py-3 px-4">Checked In</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-blue-600">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="hover:underline">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $ticket->vehicle->registration_number }}</div>
                                <div class="text-[11px] text-slate-500">{{ $ticket->vehicle->color }} {{ $ticket->vehicle->make }} {{ $ticket->vehicle->model }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                @if($ticket->guestSession && $ticket->guestSession->guest_name)
                                    <div>{{ $ticket->guestSession->guest_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $ticket->guestSession->booking_reference }}</div>
                                @else
                                    <span class="text-slate-400 italic">None</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $ticket->status->badgeClasses() }}">
                                    {{ $ticket->status->label() }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $ticket->parkingLocation ? $ticket->parkingLocation->formatted_location : '—' }}
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">
                                {{ $ticket->assignedDriver->name ?? 'Unassigned' }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                                {{ $ticket->checked_in_at->format('M d, H:i') }}
                            </td>
                            <td class="py-3 px-4 text-right space-x-1">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100" title="Details">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                                <a href="{{ route('admin.tickets.print-qr', $ticket->id) }}" class="p-1.5 rounded-lg text-amber-600 hover:text-amber-800 hover:bg-amber-50" title="Print QR">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                No tickets found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $tickets->links() }}
        </div>
    </div>

</div>
@endsection

