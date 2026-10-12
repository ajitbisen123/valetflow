@extends('layouts.driver')

@section('title', 'My Tasks')

@section('content')
<div class="space-y-6">

    <!-- Driver Header Summary -->
    <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 flex items-center justify-between">
        <div>
            <span class="text-[10px] uppercase font-bold tracking-widest text-slate-400 block">ON-DUTY DRIVER</span>
            <h2 class="text-base font-bold text-white">{{ $driver->name }}</h2>
        </div>
        <div class="text-right">
            <span class="text-xs text-amber-400 font-bold block">{{ $assignedTickets->count() + $activeRetrievals->count() }} ACTIVE TASKS</span>
            <span class="text-[10px] text-slate-500">Auto-refresh ready</span>
        </div>
    </div>

    <!-- 1. Urgent: Active Retrieval Tasks (Highest Priority) -->
    @if($activeRetrievals->isNotEmpty())
        <div class="space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-purple-400 flex items-center gap-2">
                <i class="fa-solid fa-bell animate-bounce text-purple-400"></i> Active Retrieval Tasks (Urgent)
            </h3>

            @foreach($activeRetrievals as $req)
                <div class="bg-slate-950 rounded-2xl border {{ $req->isOverdue() ? 'border-rose-500/80 shadow-rose-900/30' : 'border-purple-500/50 shadow-purple-900/20' }} p-4 shadow-lg space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs font-mono font-bold text-amber-400">Ticket #{{ $req->ticket->ticket_number }}</span>
                            <h4 class="text-lg font-black text-white font-mono mt-0.5">{{ $req->ticket->vehicle->registration_number }}</h4>
                            <p class="text-xs text-slate-400">{{ $req->ticket->vehicle->color }} {{ $req->ticket->vehicle->make }} {{ $req->ticket->vehicle->model }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold {{ $req->isOverdue() ? 'bg-rose-500 text-white animate-pulse' : 'bg-purple-900/80 text-purple-200 border border-purple-500/40' }}">
                                {{ $req->status->label() }}
                            </span>
                            <div class="text-[11px] font-semibold text-slate-300 mt-1">
                                Target: {{ $req->target_ready_at->format('H:i') }} ({{ $req->target_ready_at->diffForHumans() }})
                            </div>
                        </div>
                    </div>

                    <!-- Location to fetch from -->
                    <div class="bg-slate-900 p-3 rounded-xl border border-slate-800 text-xs flex items-center justify-between">
                        <div>
                            <span class="text-slate-500 text-[10px] uppercase block font-semibold">Location to Fetch From:</span>
                            <span class="font-bold text-white text-sm">{{ $req->ticket->parkingLocation ? $req->ticket->parkingLocation->formatted_location : 'Check Desk' }}</span>
                        </div>
                        <i class="fa-solid fa-location-dot text-amber-400 text-lg"></i>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-1">
                        @if($req->status === \App\Enums\RetrievalStatus::Assigned)
                            <form action="{{ route('driver.requests.start', $req->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-3.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-person-walking-arrow-right"></i> Start Fetching Vehicle
                                </button>
                            </form>
                        @elseif($req->status === \App\Enums\RetrievalStatus::InProgress)
                            <form action="{{ route('driver.requests.ready', $req->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-3.5 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition shadow-lg flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-flag-checkered"></i> Mark Ready at Gate
                                </button>
                            </form>
                        @elseif($req->status === \App\Enums\RetrievalStatus::ReadyAtGate)
                            <form action="{{ route('driver.requests.complete', $req->id) }}" method="POST" onsubmit="return confirm('Confirm key handover to guest and close ticket?')">
                                @csrf
                                <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition shadow-lg flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-handshake"></i> Complete Handover to Guest
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- 2. Vehicles to Park -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-2">
            <i class="fa-solid fa-car"></i> Vehicles Assigned to Park
        </h3>

        @forelse($assignedTickets as $ticket)
            <div class="bg-slate-950 rounded-2xl border border-slate-800 p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-mono font-bold text-amber-400">Ticket #{{ $ticket->ticket_number }}</span>
                        <h4 class="text-lg font-black text-white font-mono mt-0.5">{{ $ticket->vehicle->registration_number }}</h4>
                        <p class="text-xs text-slate-400">{{ $ticket->vehicle->color }} {{ $ticket->vehicle->make }} {{ $ticket->vehicle->model }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $ticket->status->badgeClasses() }}">
                        {{ $ticket->status->label() }}
                    </span>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <a href="{{ route('driver.tickets.show', $ticket->id) }}" class="flex-1 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs uppercase tracking-wider text-center transition shadow">
                        {{ $ticket->status === \App\Enums\TicketStatus::CheckedIn ? 'Start Parking' : 'Select Parking Bay' }}
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-slate-950/60 rounded-2xl border border-slate-800/80 p-6 text-center text-slate-500 text-xs">
                <i class="fa-solid fa-square-check text-2xl text-emerald-500/60 mb-2 block"></i>
                No vehicles currently waiting to be parked.
            </div>
        @endforelse
    </div>

    <!-- 3. Unassigned Retrieval Pool -->
    @if($unassignedRetrievals->isNotEmpty())
        <div class="space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-blue-400 flex items-center gap-2">
                <i class="fa-solid fa-inbox"></i> Available Retrieval Pool (Unassigned)
            </h3>

            @foreach($unassignedRetrievals as $req)
                <div class="bg-slate-950 rounded-2xl border border-slate-800 p-4 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs font-mono font-bold text-amber-400">Ticket #{{ $req->ticket->ticket_number }}</span>
                            <h4 class="text-lg font-black text-white font-mono mt-0.5">{{ $req->ticket->vehicle->registration_number }}</h4>
                            <p class="text-xs text-slate-400">Location: {{ $req->ticket->parkingLocation ? $req->ticket->parkingLocation->formatted_location : 'Desk' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-amber-400">{{ $req->requested_minutes }} Min Target</span>
                            <span class="text-[10px] text-slate-400 block">Due: {{ $req->target_ready_at->format('H:i') }}</span>
                        </div>
                    </div>

                    <form action="{{ route('driver.requests.accept', $req->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wider transition shadow">
                            <i class="fa-solid fa-check"></i> Accept This Task
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection

