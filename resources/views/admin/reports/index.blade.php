@extends('layouts.app')

@section('title', 'Operations Analytics & SLAs')

@section('content')
<div class="space-y-6">

    <!-- Header & Date Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Operations Analytics & SLAs</h1>
            <p class="text-xs text-slate-500 font-medium">Performance metrics, retrieval turnaround times, and driver workload</p>
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 shadow-sm text-xs">
            <span class="text-slate-400 font-semibold px-1">Range:</span>
            <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-slate-800">
            <span class="text-slate-400">to</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-slate-800">
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white font-bold hover:bg-slate-800 transition">
                Update
            </button>
        </form>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Checked-In</span>
            <div class="text-3xl font-black text-slate-900">{{ $metrics['total_checked_in'] }}</div>
            <p class="text-[11px] text-slate-500 mt-1">Vehicles serviced in period</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Avg Parking Time</span>
            <div class="text-3xl font-black text-blue-600">{{ $metrics['avg_parking_minutes'] }} <span class="text-sm font-semibold text-slate-500">min</span></div>
            <p class="text-[11px] text-slate-500 mt-1">Check-in to parked duration</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Avg Retrieval Time</span>
            <div class="text-3xl font-black text-purple-600">{{ $metrics['avg_retrieval_minutes'] }} <span class="text-sm font-semibold text-slate-500">min</span></div>
            <p class="text-[11px] text-slate-500 mt-1">Guest request to ready at gate</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Target SLA Hit Rate</span>
            <div class="text-3xl font-black text-emerald-600">{{ $metrics['target_met_rate'] }}%</div>
            <p class="text-[11px] text-slate-500 mt-1">Delivered on or before target</p>
        </div>

    </div>

    <!-- Driver Workload Breakdown -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
            <h2 class="font-bold text-slate-900 text-sm">Valet Driver Workload Distribution</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($drivers as $driver)
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 text-sm block">{{ $driver->name }}</span>
                            <span class="text-xs text-slate-500">{{ $driver->email }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-slate-900 block">{{ $driver->assigned_tickets_count }}</span>
                            <span class="text-[10px] text-slate-400 uppercase tracking-wider">Assigned Tasks</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection

