@extends('layouts.app')

@section('title', 'Driver & Staff Management')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Driver & Staff Management</h1>
            <p class="text-xs text-slate-500 font-medium">Manage valet driver fleet, workloads and account access</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Drivers Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                <h2 class="font-bold text-slate-900 text-sm">Active Driver Fleet</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-600 font-semibold uppercase tracking-wider text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Driver Name</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Active Tasks</th>
                            <th class="py-3 px-4">Completed Today</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($drivers as $driver)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-bold text-slate-900 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-[10px]">
                                        {{ substr($driver->name, 0, 1) }}
                                    </span>
                                    {{ $driver->name }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600">{{ $driver->email }}</td>
                                <td class="py-3 px-4">
                                    @if($driver->active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            Deactivated
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    {{ $driver->assigned_tickets_count }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $driver->completed_today_count }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('admin.drivers.toggle', $driver->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $driver->active ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                            {{ $driver->active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Driver Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h2 class="font-bold text-slate-900 text-sm">Add New Valet Driver</h2>
            <p class="text-xs text-slate-500">Create an authenticated driver account for mobile portal access</p>

            <form action="{{ route('admin.drivers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Liam Walker"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" required placeholder="driver@valetflow.test"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Temporary Password</label>
                    <input type="password" name="password" required placeholder="Minimum 6 characters"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-amber-500">
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition shadow">
                    Create Driver Account
                </button>
            </form>
        </div>

    </div>

</div>
@endsection

