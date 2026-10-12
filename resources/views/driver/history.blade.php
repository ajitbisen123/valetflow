@extends('layouts.driver')

@section('title', 'Task History')

@section('content')
<div class="space-y-4">

    <div>
        <h2 class="text-xl font-bold text-white">Completed Task History</h2>
        <p class="text-xs text-slate-400">Recently handed over vehicles</p>
    </div>

    <div class="space-y-3">
        @forelse($history as $ticket)
            <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-xs font-mono font-bold text-amber-400">#{{ $ticket->ticket_number }}</span>
                    <h3 class="text-base font-bold text-white font-mono">{{ $ticket->vehicle->registration_number }}</h3>
                    <p class="text-[11px] text-slate-400">{{ $ticket->vehicle->color }} {{ $ticket->vehicle->make }}</p>
                </div>
                <div class="text-right">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-emerald-400 border border-slate-700">
                        HANDED OVER
                    </span>
                    <p class="text-[10px] text-slate-500 font-mono mt-1">
                        {{ $ticket->handed_over_at ? $ticket->handed_over_at->diffForHumans() : 'Closed' }}
                    </p>
                </div>
            </div>
        @empty
            <div class="bg-slate-950 p-8 rounded-2xl border border-slate-800 text-center text-slate-500 text-xs">
                No completed tasks recorded yet today.
            </div>
        @endforelse
    </div>

</div>
@endsection

