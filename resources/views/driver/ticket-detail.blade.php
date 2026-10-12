@extends('layouts.driver')

@section('title', "Task #{$ticket->ticket_number}")

@section('content')
<div class="space-y-6">

    <!-- Back Navigation -->
    <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center gap-2 text-xs text-slate-400 hover:text-white transition">
        <i class="fa-solid fa-arrow-left"></i> Back to Tasks
    </a>

    <!-- Ticket Summary Card -->
    <div class="bg-slate-950 rounded-2xl border border-slate-800 p-5 space-y-4">
        <div class="flex items-start justify-between">
            <div>
                <span class="text-xs font-mono font-bold text-amber-400">TICKET #{{ $ticket->ticket_number }}</span>
                <h2 class="text-2xl font-black text-white font-mono mt-0.5">{{ $ticket->vehicle->registration_number }}</h2>
                <p class="text-xs text-slate-400">{{ $ticket->vehicle->color }} {{ $ticket->vehicle->make }} {{ $ticket->vehicle->model }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $ticket->status->badgeClasses() }}">
                {{ $ticket->status->label() }}
            </span>
        </div>

        @if($ticket->vehicle->notes_private)
            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300">
                <span class="font-bold text-amber-400 block mb-0.5">Note:</span>
                {{ $ticket->vehicle->notes_private }}
            </div>
        @endif
    </div>

    <!-- Parking Workflow Actions -->
    @if($ticket->status === \App\Enums\TicketStatus::CheckedIn)
        <div class="bg-slate-950 rounded-2xl border border-slate-800 p-5 text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto text-xl">
                <i class="fa-solid fa-key"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Ready to Move Vehicle?</h3>
                <p class="text-xs text-slate-400 mt-1">Take the car key and begin transit to the parking structure.</p>
            </div>

            <form action="{{ route('driver.tickets.start-parking', $ticket->id) }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-sm uppercase tracking-wider transition shadow-lg shadow-amber-500/20">
                    <i class="fa-solid fa-car-side mr-2"></i> Start Parking Transit
                </button>
            </form>
        </div>
    @elseif($ticket->status === \App\Enums\TicketStatus::ParkingInProgress)
        <!-- Bay Allocation Form -->
        <div class="bg-slate-950 rounded-2xl border border-slate-800 p-5 space-y-4">
            <div>
                <h3 class="text-base font-bold text-white">Record Parking Bay Location</h3>
                <p class="text-xs text-slate-400 mt-0.5">Select the exact parking bay where you parked this car.</p>
            </div>

            <form action="{{ route('driver.tickets.mark-parked', $ticket->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-amber-400 uppercase tracking-wider mb-2">
                        Select Parking Bay / Slot <span class="text-rose-500">*</span>
                    </label>
                    <select name="parking_location_id" required class="w-full p-3.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:border-amber-500 font-medium">
                        <option value="">-- Choose Parking Location --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $ticket->parking_location_id == $loc->id ? 'selected' : '' }}>
                                {{ $loc->formatted_location }} ({{ $loc->description }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full py-4 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-sm uppercase tracking-wider transition shadow-lg shadow-emerald-500/20">
                    <i class="fa-solid fa-square-parking mr-2"></i> Mark as Parked & Save Bay
                </button>
            </form>
        </div>
    @else
        <!-- Already Parked or Active Retrieval -->
        <div class="bg-slate-950 rounded-2xl border border-slate-800 p-5 space-y-3">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Current Parking Bay</h3>
            <div class="text-base font-bold text-white">
                {{ $ticket->parkingLocation ? $ticket->parkingLocation->formatted_location : 'Unallocated' }}
            </div>
            <p class="text-xs text-slate-400">
                Parked since: {{ $ticket->parked_at ? $ticket->parked_at->format('M d, H:i') : 'N/A' }}
            </p>
        </div>
    @endif

</div>
@endsection

