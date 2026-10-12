@extends('layouts.guest')

@section('title', "Valet Pass #{$ticket->ticket_number}")

@section('content')
<div class="space-y-6">

    <!-- Digital Ticket Card -->
    <div class="bg-slate-950 rounded-3xl border border-slate-800 p-6 shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Ticket Header -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div>
                <span class="text-[10px] tracking-widest text-slate-400 uppercase font-bold">DIGITAL PASS NUMBER</span>
                <div class="text-2xl font-black font-mono text-amber-400 mt-0.5">#{{ $ticket->ticket_number }}</div>
            </div>
            <div class="text-right">
                <span id="ticket-badge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $ticket->status->badgeClasses() }}">
                    {{ $ticket->status->label() }}
                </span>
            </div>
        </div>

        <!-- Vehicle Details (Safe Fields Only) -->
        <div class="py-4 space-y-2 text-xs">
            <div class="flex justify-between items-baseline">
                <span class="text-slate-400">Vehicle Registration:</span>
                <span class="font-bold text-white font-mono text-sm">{{ $vehicle->registration_number }}</span>
            </div>
            @if($vehicle->make || $vehicle->model)
                <div class="flex justify-between">
                    <span class="text-slate-400">Vehicle Make/Model:</span>
                    <span class="text-slate-300 font-medium">{{ $vehicle->color }} {{ $vehicle->make }} {{ $vehicle->model }}</span>
                </div>
            @endif
            <div class="flex justify-between text-[11px] text-slate-400 pt-1">
                <span>Last Updated:</span>
                <span id="last-updated" class="font-mono">{{ $ticket->updated_at->diffForHumans() }}</span>
            </div>
        </div>

        <!-- Dynamic Section Based on State -->
        <div class="pt-2">

            @if($ticket->status === \App\Enums\TicketStatus::Parked && !$activeRequest)
                <!-- Request My Car Choice (5, 10, 15 Minutes) -->
                <div class="space-y-4 pt-3 border-t border-slate-800">
                    <div class="text-center">
                        <h2 class="text-base font-black text-white">Ready for your car?</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Select when you would like your vehicle brought to the valet gate:</p>
                    </div>

                    <form action="{{ route('guest.retrieval-request', $token) }}" method="POST" id="retrieval-form" class="space-y-4">
                        @csrf
                        <input type="hidden" name="idempotency_key" id="idempotency_key" value="">

                        <!-- 3 Large Buttons -->
                        <div class="grid grid-cols-3 gap-2.5">
                            <label class="cursor-pointer">
                                <input type="radio" name="requested_minutes" value="5" class="peer sr-only" required>
                                <div class="p-3.5 rounded-2xl bg-slate-900 border-2 border-slate-800 peer-checked:border-amber-500 peer-checked:bg-amber-500/10 text-center transition active:scale-95 shadow">
                                    <span class="block text-2xl font-black text-white peer-checked:text-amber-400">5</span>
                                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Minutes</span>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="requested_minutes" value="10" checked class="peer sr-only">
                                <div class="p-3.5 rounded-2xl bg-slate-900 border-2 border-slate-800 peer-checked:border-amber-500 peer-checked:bg-amber-500/10 text-center transition active:scale-95 shadow">
                                    <span class="block text-2xl font-black text-white peer-checked:text-amber-400">10</span>
                                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Minutes</span>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="requested_minutes" value="15" class="peer sr-only">
                                <div class="p-3.5 rounded-2xl bg-slate-900 border-2 border-slate-800 peer-checked:border-amber-500 peer-checked:bg-amber-500/10 text-center transition active:scale-95 shadow">
                                    <span class="block text-2xl font-black text-white peer-checked:text-amber-400">15</span>
                                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Minutes</span>
                                </div>
                            </label>
                        </div>

                        <!-- Honest Disclaimer -->
                        <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-[11px] text-amber-200/90 leading-tight text-center">
                            <i class="fa-solid fa-circle-info mr-1"></i> These are target preparation times for our valet team, not guaranteed SLAs.
                        </div>

                        <!-- Confirm Button -->
                        <button type="submit" id="submit-btn" class="w-full py-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-sm uppercase tracking-wider transition shadow-xl shadow-amber-500/20 flex items-center justify-center gap-2 active:scale-98">
                            <i class="fa-solid fa-car"></i> Request My Vehicle
                        </button>
                    </form>
                </div>

            @elseif($activeRequest)
                <!-- Request Tracking Timeline -->
                <div class="space-y-5 pt-3 border-t border-slate-800">
                    <div class="text-center">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-amber-400 block">RETRIEVAL IN PROGRESS</span>
                        <h3 class="text-lg font-black text-white mt-0.5">
                            Target Time: {{ $activeRequest->target_ready_at->format('H:i') }}
                        </h3>
                        <p class="text-xs text-slate-400">Target is {{ $activeRequest->target_ready_at->diffForHumans() }}</p>
                    </div>

                    <!-- Visual Progress Timeline -->
                    <div class="space-y-3 py-2 text-xs">
                        <!-- Step 1 -->
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-emerald-500 text-slate-950 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="flex-1">
                                <span class="font-bold text-white block">Request Received</span>
                                <span class="text-[10px] text-slate-400">{{ $activeRequest->requested_at->format('H:i') }}</span>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full {{ in_array($activeRequest->status->value, ['ASSIGNED', 'IN_PROGRESS', 'READY_AT_GATE', 'COMPLETED']) ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-500' }} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                @if(in_array($activeRequest->status->value, ['ASSIGNED', 'IN_PROGRESS', 'READY_AT_GATE', 'COMPLETED']))
                                    <i class="fa-solid fa-check"></i>
                                @else
                                    2
                                @endif
                            </div>
                            <div class="flex-1">
                                <span class="font-bold {{ in_array($activeRequest->status->value, ['ASSIGNED', 'IN_PROGRESS', 'READY_AT_GATE', 'COMPLETED']) ? 'text-white' : 'text-slate-500' }} block">Valet Assigned</span>
                                <span class="text-[10px] text-slate-400">Driver designated</span>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full {{ in_array($activeRequest->status->value, ['IN_PROGRESS', 'READY_AT_GATE', 'COMPLETED']) ? 'bg-purple-500 text-white' : 'bg-slate-800 text-slate-500' }} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                @if(in_array($activeRequest->status->value, ['READY_AT_GATE', 'COMPLETED']))
                                    <i class="fa-solid fa-check"></i>
                                @else
                                    <i class="fa-solid fa-person-walking"></i>
                                @endif
                            </div>
                            <div class="flex-1">
                                <span class="font-bold {{ in_array($activeRequest->status->value, ['IN_PROGRESS', 'READY_AT_GATE', 'COMPLETED']) ? 'text-purple-300 font-bold' : 'text-slate-500' }} block">Driver Fetching Car</span>
                                <span class="text-[10px] text-slate-400">Moving to collection gate</span>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full {{ in_array($activeRequest->status->value, ['READY_AT_GATE', 'COMPLETED']) ? 'bg-teal-400 text-slate-950 animate-pulse' : 'bg-slate-800 text-slate-500' }} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                <i class="fa-solid fa-flag-checkered"></i>
                            </div>
                            <div class="flex-1">
                                <span class="font-bold {{ in_array($activeRequest->status->value, ['READY_AT_GATE', 'COMPLETED']) ? 'text-teal-300 text-sm font-black' : 'text-slate-500' }} block">
                                    Ready at Valet Gate
                                </span>
                                <span class="text-[10px] text-slate-400">Car arrived at front drive</span>
                            </div>
                        </div>
                    </div>

                    @if($ticket->status === \App\Enums\TicketStatus::ReadyAtGate)
                        <div class="p-4 rounded-2xl bg-teal-500/20 border border-teal-500/40 text-center space-y-1 animate-pulse">
                            <h4 class="font-black text-teal-300 text-base">Your vehicle is waiting at the main entrance gate!</h4>
                            <p class="text-xs text-teal-100">Please present this screen to the valet desk attendant for key handover.</p>
                        </div>
                    @endif
                </div>

            @elseif($ticket->status === \App\Enums\TicketStatus::HandedOver)
                <!-- Handed over -->
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 text-center space-y-2">
                    <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Vehicle Handed Over</h3>
                    <p class="text-xs text-slate-400">Thank you for visiting Grand Azure Luxury Resort. Have a safe journey!</p>
                </div>
            @else
                <!-- Checked in or Parking in progress -->
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 text-center space-y-2">
                    <i class="fa-solid fa-shield-halved text-2xl text-amber-400 mb-1 block"></i>
                    <h3 class="text-sm font-bold text-white">Vehicle in Valet Care</h3>
                    <p class="text-xs text-slate-400">
                        Your vehicle is currently being safely parked in our secure hotel garage. Once parked, you can request it anytime from this pass.
                    </p>
                </div>
            @endif

        </div>

    </div>

</div>

@push('scripts')
<script>
    // Generate idempotency key for form submit to prevent double-taps
    const form = document.getElementById('retrieval-form');
    if (form) {
        document.getElementById('idempotency_key').value = 'req_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
        form.addEventListener('submit', function() {
            const btn = document.getElementById('submit-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Submitting Request...';
            }
        });
    }

    // Auto-polling status every 7 seconds
    setInterval(function() {
        fetch("{{ route('guest.status', $token) }}")
            .then(res => res.json())
            .then(data => {
                if (data.is_ready_at_gate || data.is_handed_over || data.ticket_status !== "{{ $ticket->status->value }}") {
                    // State changed! Refresh to show latest progress timeline
                    window.location.reload();
                }
            })
            .catch(() => {});
    }, 7000);
</script>
@endpush
@endsection

