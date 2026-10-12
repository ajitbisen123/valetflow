@extends('layouts.app')

@section('title', 'New Valet Check-In')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Vehicle Check-In</h1>
            <p class="text-xs text-slate-500 font-medium">Issue digital valet parking pass and record guest arrival</p>
        </div>
        <a href="{{ route('admin.tickets.index') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Registry
        </a>
    </div>

    <form action="{{ route('admin.tickets.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-6">
        @csrf

        <!-- Section 1: Vehicle Information -->
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-amber-600 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-car"></i> 1. Vehicle Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="registration_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Vehicle License / Registration Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="registration_number" name="registration_number" value="{{ old('registration_number') }}" required placeholder="e.g. MH-02-AZ-9988"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl font-mono font-bold text-base text-slate-900 uppercase focus:bg-white focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                </div>

                <div>
                    <label for="make" class="block text-xs font-semibold text-slate-600 mb-1.5">Vehicle Make</label>
                    <input type="text" id="make" name="make" value="{{ old('make') }}" placeholder="e.g. Mercedes, BMW, Toyota"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                </div>

                <div>
                    <label for="model" class="block text-xs font-semibold text-slate-600 mb-1.5">Vehicle Model</label>
                    <input type="text" id="model" name="model" value="{{ old('model') }}" placeholder="e.g. S-Class, X5, Camry"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                </div>

                <div>
                    <label for="color" class="block text-xs font-semibold text-slate-600 mb-1.5">Vehicle Color</label>
                    <input type="text" id="color" name="color" value="{{ old('color') }}" placeholder="e.g. Black, White, Silver"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                </div>

                <div>
                    <label for="assigned_driver_id" class="block text-xs font-semibold text-slate-600 mb-1.5">Assign Valet Driver (Optional)</label>
                    <select id="assigned_driver_id" name="assigned_driver_id"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                        <option value="">-- Assign Later / Driver Pool --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ old('assigned_driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }} ({{ $driver->assigned_tickets_count }} active tasks)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Guest Information (Optional) -->
        <div class="pt-4 border-t border-slate-100">
            <h2 class="text-xs font-bold uppercase tracking-wider text-amber-600 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user"></i> 2. Guest Information (Optional)
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="guest_name" class="block text-xs font-semibold text-slate-600 mb-1.5">Guest Name</label>
                    <input type="text" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" placeholder="e.g. Mr. Robert Taylor"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                </div>

                <div>
                    <label for="booking_reference" class="block text-xs font-semibold text-slate-600 mb-1.5">Room / Booking Reference</label>
                    <input type="text" id="booking_reference" name="booking_reference" value="{{ old('booking_reference') }}" placeholder="e.g. Suite 402 / BK-9021"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                </div>

                <div class="md:col-span-2">
                    <label for="notes_private" class="block text-xs font-semibold text-slate-600 mb-1.5">Internal Private Staff Notes</label>
                    <textarea id="notes_private" name="notes_private" rows="2" placeholder="e.g. Existing scratch on front bumper, key tracker attached"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">{{ old('notes_private') }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">These notes are visible to hotel staff and drivers only; not shown on the guest QR pass.</p>
                </div>
            </div>
        </div>

        <!-- Submit & Print -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('admin.tickets.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs tracking-wider uppercase transition shadow-lg shadow-amber-500/20 flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Generate & Print Guest QR Pass
            </button>
        </div>

    </form>
</div>
@endsection

