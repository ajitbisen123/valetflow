@extends('layouts.guest')

@section('title', 'Pass Invalid')

@section('content')
<div class="bg-slate-950 rounded-3xl border border-slate-800 p-8 shadow-2xl text-center space-y-4">
    <div class="w-16 h-16 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center mx-auto text-2xl">
        <i class="fa-solid fa-triangle-exclamation"></i>
    </div>

    <h2 class="text-xl font-bold text-white">Invalid or Expired Valet Pass</h2>

    <p class="text-xs text-slate-400 leading-relaxed">
        {{ $message ?? 'This parking pass is invalid, expired, or has been revoked. If you are a guest currently staying with us, please present your room key or physical claim card at the hotel valet reception desk.' }}
    </p>

    <div class="pt-4 border-t border-slate-800 text-[11px] text-slate-500">
        Grand Azure Luxury Resort &bull; Front Valet Concierge Desk
    </div>
</div>
@endsection

