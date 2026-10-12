@extends('layouts.app')

@section('title', 'Parking Locations Setup')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Parking Locations Setup</h1>
            <p class="text-xs text-slate-500 font-medium">Configure hotel parking zones, decks, and individual bay slots</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Locations List -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                <h2 class="font-bold text-slate-900 text-sm">Configured Parking Bays</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-600 font-semibold uppercase tracking-wider text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Zone / Deck</th>
                            <th class="py-3 px-4">Floor</th>
                            <th class="py-3 px-4">Slot</th>
                            <th class="py-3 px-4">Occupancy</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($locations as $loc)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $loc->zone }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $loc->floor ?? '—' }}</td>
                                <td class="py-3 px-4 font-mono font-semibold text-slate-800">{{ $loc->slot ?? 'Open' }}</td>
                                <td class="py-3 px-4">
                                    @if($loc->tickets_count > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            Occupied ({{ $loc->tickets_count }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                            Available
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($loc->active)
                                        <span class="text-emerald-600 font-semibold text-[11px]">Active</span>
                                    @else
                                        <span class="text-rose-600 font-semibold text-[11px]">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('admin.locations.toggle', $loc->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $loc->active ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                            {{ $loc->active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Location Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h2 class="font-bold text-slate-900 text-sm">Add New Parking Bay</h2>
            <p class="text-xs text-slate-500">Define a parking zone and optional floor/slot</p>

            <form action="{{ route('admin.locations.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Zone Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="zone" required placeholder="e.g. Zone C - East Deck"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Floor / Level (Optional)</label>
                    <input type="text" name="floor" placeholder="e.g. Level 3 or Basement 1"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Slot Identifier (Optional)</label>
                    <input type="text" name="slot" placeholder="e.g. C-301"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description / Wayfinding</label>
                    <textarea name="description" rows="2" placeholder="e.g. Near staff elevator, wide bay"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition shadow">
                    Save Parking Bay
                </button>
            </form>
        </div>

    </div>

</div>
@endsection

