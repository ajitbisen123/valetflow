<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ValetFlow - QR-Based Hotel Valet Parking System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex flex-col justify-between selection:bg-amber-500 selection:text-slate-950">

    <!-- Top Navigation -->
    <header class="border-b border-slate-800/80 px-6 py-4 bg-slate-900/60 backdrop-blur sticky top-0 z-30">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 to-amber-400 flex items-center justify-center text-slate-950 font-black text-xl shadow-lg shadow-amber-500/20">
                    VF
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white tracking-wider flex items-center gap-2">
                        VALETFLOW
                        <span class="text-[10px] bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2 py-0.5 rounded-full font-mono">v1.0 Live</span>
                    </h1>
                    <p class="text-xs text-slate-400 font-medium">QR-Based Hotel Valet Parking Management</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ Auth::user()->role === \App\Enums\UserRole::Driver ? route('driver.dashboard') : route('admin.dashboard') }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm shadow transition">
                        Open My Portal ({{ Auth::user()->name }})
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm shadow transition">
                        Staff Login
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="max-w-6xl mx-auto px-6 py-10 w-full flex-1">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800 text-amber-400 text-xs font-semibold border border-slate-700 mb-4">
                <i class="fa-solid fa-hotel"></i> Grand Azure Luxury Resort Demo Pilot
            </div>
            <h2 class="text-3xl md:text-5xl font-extrabold text-white tracking-tight mb-4">
                Real-Time Valet Operations & <span class="text-amber-400">Guest QR Retrieval</span>
            </h2>
            <p class="text-slate-400 text-sm md:text-base leading-relaxed">
                Experience end-to-end valet lifecycle: reception check-in, driver parking assignment, guest 5/10/15 min retrieval requests, and gate handover.
            </p>
        </div>

        <!-- Quick Experience Portal Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">

            <!-- Card 1: Admin & Reception Desk -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 flex flex-col justify-between hover:border-amber-500/40 transition shadow-xl group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Operations Desk</h3>
                    <p class="text-xs text-slate-400 mb-4">
                        Reception check-in, live parking board, printable guest QR passes, driver workload, and audit logs.
                    </p>
                    <div class="bg-slate-950/80 rounded-xl p-3 border border-slate-800/80 text-xs text-slate-300 font-mono mb-4 space-y-1">
                        <div><span class="text-slate-500">User:</span> admin@valetflow.test</div>
                        <div><span class="text-slate-500">Pass:</span> password</div>
                    </div>
                </div>
                <div class="space-y-2">
                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="admin@valetflow.test">
                        <input type="hidden" name="password" value="password">
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs tracking-wider uppercase transition shadow flex items-center justify-center gap-2">
                            <span>1-Click Launch Admin Desk</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>
                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="reception@valetflow.test">
                        <input type="hidden" name="password" value="password">
                        <button type="submit" class="w-full py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs transition">
                            Login as Receptionist
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card 2: Driver Mobile Portal -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 flex flex-col justify-between hover:border-amber-500/40 transition shadow-xl group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition">
                        <i class="fa-solid fa-mobile-screen"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Driver Mobile Portal</h3>
                    <p class="text-xs text-slate-400 mb-4">
                        Mobile-first tasks: locate vehicle, select parking zone/slot, accept retrieval tasks, mark ready at gate.
                    </p>
                    <div class="bg-slate-950/80 rounded-xl p-3 border border-slate-800/80 text-xs text-slate-300 font-mono mb-4 space-y-1">
                        <div><span class="text-slate-500">User:</span> driver1@valetflow.test</div>
                        <div><span class="text-slate-500">Pass:</span> password</div>
                    </div>
                </div>
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="driver1@valetflow.test">
                    <input type="hidden" name="password" value="password">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs tracking-wider uppercase transition shadow flex items-center justify-center gap-2">
                        <span>1-Click Launch Driver App</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>

            <!-- Card 3: Guest QR Experience -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 flex flex-col justify-between hover:border-amber-500/40 transition shadow-xl group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Guest QR Retrieval Pass</h3>
                    <p class="text-xs text-slate-400 mb-4">
                        Scan & test the guest experience on your phone or browser: view car status, select 5/10/15 minutes, and track retrieval progress in real-time.
                    </p>
                    @if($sampleTicket)
                        <div class="bg-slate-950/80 rounded-xl p-3 border border-slate-800/80 text-xs text-slate-300 mb-4">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-white">Ticket #{{ $sampleTicket->ticket_number }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-950 text-emerald-400 border border-emerald-800">PARKED</span>
                            </div>
                            <div class="text-slate-400 text-[11px] mt-1">{{ $sampleTicket->vehicle->registration_number }} &bull; {{ $sampleTicket->vehicle->make }} {{ $sampleTicket->vehicle->model }}</div>
                        </div>
                    @endif
                </div>
                @php
                    $guestUrl = null;
                    if ($sampleTicket) {
                        $tokenRecord = \App\Models\QrToken::where('ticket_id', $sampleTicket->id)->whereNull('revoked_at')->first();
                        if ($tokenRecord) {
                            // Find or generate a temporary test pass
                            $gen = app(\App\Services\QrTokenService::class)->generateForTicket($sampleTicket);
                            $guestUrl = route('guest.show', $gen['token']);
                        }
                    }
                @endphp
                @if($guestUrl)
                    <a href="{{ $guestUrl }}" target="_blank" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs tracking-wider uppercase transition shadow flex items-center justify-center gap-2">
                        <span>Open Guest Pass</span>
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                @else
                    <a href="{{ route('admin.tickets.create') }}" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs text-center transition">
                        Create First Ticket
                    </a>
                @endif
            </div>

        </div>

        <!-- System Architecture & Verification Badges -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 text-xs text-slate-400">
            <h4 class="font-bold text-sm text-slate-200 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-server text-amber-400"></i> Local Environment Status & Architecture
            </h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 font-mono text-[11px]">
                <div class="bg-slate-950 p-2.5 rounded-lg border border-slate-800">
                    <span class="text-slate-500 block">PHP Runtime:</span>
                    <span class="text-amber-400 font-bold">PHP 8.3.33 (CLI)</span>
                </div>
                <div class="bg-slate-950 p-2.5 rounded-lg border border-slate-800">
                    <span class="text-slate-500 block">Framework:</span>
                    <span class="text-amber-400 font-bold">Laravel 13.x</span>
                </div>
                <div class="bg-slate-950 p-2.5 rounded-lg border border-slate-800">
                    <span class="text-slate-500 block">Database:</span>
                    <span class="text-emerald-400 font-bold">Connected & Seeded</span>
                </div>
                <div class="bg-slate-950 p-2.5 rounded-lg border border-slate-800">
                    <span class="text-slate-500 block">QR Engine:</span>
                    <span class="text-emerald-400 font-bold">Simple-QRCode / SVG</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 px-6 py-4 text-center text-xs text-slate-500 bg-slate-950">
        ValetFlow &bull; QR-Based Hotel Valet Parking System &bull; Version 1.0
    </footer>

</body>
</html>
