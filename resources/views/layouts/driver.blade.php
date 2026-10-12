<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Driver Portal') - ValetFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 font-sans antialiased min-h-screen flex flex-col pb-20">

    <!-- Top Bar -->
    <header class="bg-slate-950 px-4 py-3 border-b border-slate-800 flex items-center justify-between sticky top-0 z-30 shadow">
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-sm shadow">VF</span>
            <div>
                <h1 class="text-sm font-bold text-white tracking-wide">DRIVER PORTAL</h1>
                <p class="text-[10px] text-amber-400 font-medium truncate max-w-[150px]">{{ Auth::user()->name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('driver.scan') }}" class="px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 text-xs font-bold flex items-center gap-1.5 shadow active:scale-95 transition">
                <i class="fa-solid fa-qrcode"></i> Scan QR
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-8 h-8 rounded-lg bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center text-xs">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </header>

    <!-- Main Mobile Content Area -->
    <main class="flex-1 p-4 max-w-lg w-full mx-auto">
        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="mb-4 p-3.5 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-sm flex items-center gap-2.5">
                <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3.5 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-300 text-sm flex items-center gap-2.5">
                <i class="fa-solid fa-circle-exclamation text-rose-400 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Fixed Bottom Mobile Navigation Bar -->
    <nav class="fixed bottom-0 inset-x-0 bg-slate-950 border-t border-slate-800 z-40 px-4 py-2 flex items-center justify-around shadow-2xl">
        <a href="{{ route('driver.dashboard') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg transition {{ request()->routeIs('driver.dashboard') ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
            <i class="fa-solid fa-list-check text-lg"></i>
            <span class="text-[11px]">Tasks</span>
        </a>

        <a href="{{ route('driver.scan') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg transition {{ request()->routeIs('driver.scan') ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
            <i class="fa-solid fa-camera text-lg"></i>
            <span class="text-[11px]">Scanner</span>
        </a>

        <a href="{{ route('driver.history') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg transition {{ request()->routeIs('driver.history') ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
            <i class="fa-solid fa-clock-rotate-left text-lg"></i>
            <span class="text-[11px]">History</span>
        </a>

        @if(Auth::user()->canAccessAdminPanel())
            <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-slate-400 hover:text-slate-200">
                <i class="fa-solid fa-desktop text-lg"></i>
                <span class="text-[11px]">Desk</span>
            </a>
        @endif
    </nav>

    @stack('scripts')
</body>
</html>

