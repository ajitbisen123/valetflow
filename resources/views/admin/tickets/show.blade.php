@extends('layouts.app')

@section('title', "Ticket #{$ticket->ticket_number}")

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 font-mono tracking-tight">{{ $ticket->ticket_number }}</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $ticket->status->badgeClasses() }}">
                    {{ $ticket->status->label() }}
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">
                Checked in {{ $ticket->checked_in_at->format('M d, Y H:i:s') }} ({{ $ticket->checked_in_at->diffForHumans() }})
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.tickets.print-qr', $ticket->id) }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition shadow flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Print Guest QR Pass
            </a>
            <form action="{{ route('admin.tickets.reissue-qr', $ticket->id) }}" method="POST" onsubmit="return confirm('Revoke existing guest QR token and generate a new one?')">
                @csrf
                <button type="submit" class="px-3.5 py-2.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 font-semibold text-xs transition flex items-center gap-1.5" title="Lost Card Resolution">
                    <i class="fa-solid fa-rotate"></i> Reissue QR
                </button>
            </form>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1 & 2: Vehicle & Operations info -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Vehicle & Guest Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Vehicle & Guest Details</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Registration Number</span>
                        <span class="text-base font-black font-mono text-slate-900">{{ $ticket->vehicle->registration_number }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Make & Model</span>
                        <span class="font-bold text-slate-800">{{ $ticket->vehicle->make ?? '—' }} {{ $ticket->vehicle->model ?? '' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Color</span>
                        <span class="font-bold text-slate-800">{{ $ticket->vehicle->color ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Guest Name</span>
                        <span class="font-bold text-slate-800">{{ $ticket->guestSession->guest_name ?? 'Not recorded' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Room / Booking Ref</span>
                        <span class="font-bold text-slate-800 font-mono">{{ $ticket->guestSession->booking_reference ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Reception Staff</span>
                        <span class="font-bold text-slate-800">{{ $ticket->creator->name ?? 'System' }}</span>
                    </div>
                </div>

                @if($ticket->vehicle->notes_private)
                    <div class="pt-3 border-t border-slate-100 text-xs bg-slate-50 p-3 rounded-xl">
                        <span class="font-bold text-slate-700 block mb-1">Private Staff Notes:</span>
                        <p class="text-slate-600">{{ $ticket->vehicle->notes_private }}</p>
                    </div>
                @endif
            </div>

            <!-- Parking & Driver Assignment Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Valet Assignment & Bay Allocation</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                    <!-- Current Location -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[11px] block uppercase font-semibold mb-1">Current Parking Location</span>
                        @if($ticket->parkingLocation)
                            <div class="text-sm font-bold text-slate-900">{{ $ticket->parkingLocation->formatted_location }}</div>
                            <div class="text-[11px] text-slate-500 mt-1">{{ $ticket->parkingLocation->description }}</div>
                            @if($ticket->parked_at)
                                <div class="text-[10px] text-slate-400 mt-2">Parked at: {{ $ticket->parked_at->format('M d, H:i') }}</div>
                            @endif
                        @else
                            <div class="text-slate-400 italic">No parking bay assigned yet.</div>
                        @endif
                    </div>

                    <!-- Driver Assignment -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[11px] block uppercase font-semibold mb-1">Assigned Driver</span>
                        <div class="text-sm font-bold text-slate-900 mb-2">
                            {{ $ticket->assignedDriver ? $ticket->assignedDriver->name : 'Unassigned' }}
                        </div>

                        <!-- Reassign Form -->
                        <form action="{{ route('admin.tickets.assign-driver', $ticket->id) }}" method="POST" class="flex gap-2">
                            @csrf
                            <select name="driver_id" required class="flex-1 px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs text-slate-900">
                                <option value="">-- Choose Driver --</option>
                                @foreach($drivers as $d)
                                    <option value="{{ $d->id }}" {{ $ticket->assigned_driver_id == $d->id ? 'selected' : '' }}>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs">
                                Assign
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Immutable Event Timeline -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Immutable Audit Timeline</h2>
                <div class="space-y-4">
                    @foreach($ticket->events as $event)
                        <div class="flex gap-3 text-xs">
                            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-[10px] flex-shrink-0 mt-0.5">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-baseline justify-between">
                                    <span class="font-bold text-slate-900">
                                        {{ $event->old_status ? $event->old_status . ' → ' : '' }}{{ $event->new_status }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $event->created_at->format('M d, H:i:s') }}</span>
                                </div>
                                <p class="text-slate-600 mt-0.5">{{ $event->reason }}</p>
                                <span class="text-[10px] text-slate-400">Actor: {{ $event->actor ? $event->actor->name : 'Guest (Via QR Pass)' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Column 3: Active Guest Request & QR Pass Preview -->
        <div class="space-y-6">

            <!-- Active Retrieval Request Status -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Guest Retrieval Status</h2>
                @if($ticket->activeRetrievalRequest)
                    <div class="p-4 rounded-xl {{ $ticket->activeRetrievalRequest->isOverdue() ? 'bg-rose-50 border border-rose-200 text-rose-900' : 'bg-purple-50 border border-purple-200 text-purple-900' }}">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold uppercase text-[10px] tracking-wider">Requested Target</span>
                            <span class="font-black text-sm">{{ $ticket->activeRetrievalRequest->requested_minutes }} Minutes</span>
                        </div>
                        <div class="text-xs mb-2">
                            Requested at: {{ $ticket->activeRetrievalRequest->requested_at->format('H:i:s') }}
                        </div>
                        <div class="text-xs font-semibold">
                            Target Ready: {{ $ticket->activeRetrievalRequest->target_ready_at->format('H:i:s') }}
                            ({{ $ticket->activeRetrievalRequest->target_ready_at->diffForHumans() }})
                        </div>
                        @if($ticket->activeRetrievalRequest->isOverdue())
                            <div class="mt-2 text-xs font-bold text-rose-700 bg-rose-100 p-2 rounded-lg flex items-center gap-1.5">
                                <i class="fa-solid fa-triangle-exclamation"></i> OVERDUE - Dispatcher Attention Required
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">No active retrieval request placed by guest yet.</p>
                @endif
            </div>

            <!-- Active QR Token Info -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Security & QR Token</h2>
                <div class="text-xs space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Token Status:</span>
                        <span class="font-bold text-emerald-600">Active & Valid</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Token Hash:</span>
                        <span class="font-mono text-[10px] text-slate-600 truncate max-w-[150px]">{{ $ticket->activeQrToken?->token_hash ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Last Scanned:</span>
                        <span class="text-slate-700">{{ $ticket->activeQrToken?->last_used_at?->diffForHumans() ?? 'Never' }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

