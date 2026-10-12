<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Valet Parking Pass') - ValetFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- Top Hotel Header -->
    <header class="bg-slate-950/90 backdrop-blur border-b border-slate-800/80 px-4 py-4 text-center sticky top-0 z-20">
        <div class="max-w-md mx-auto flex items-center justify-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500 text-slate-950 font-black flex items-center justify-center text-lg shadow-md shadow-amber-500/20">
                VF
            </div>
            <div class="text-left">
                <p class="text-xs tracking-widest text-amber-400 font-semibold uppercase">HOTEL VALET SERVICE</p>
                <h1 class="text-sm font-bold text-white tracking-wide truncate max-w-[240px]">Grand Azure Luxury Resort</h1>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 p-4 max-w-md w-full mx-auto my-auto flex flex-col justify-center">
        @if(session('success'))
            <div class="mb-4 p-4 rounded-2xl bg-emerald-950/90 border border-emerald-500/40 text-emerald-300 text-sm flex items-center gap-3 shadow-lg">
                <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                <div class="font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 rounded-2xl bg-rose-950/90 border border-rose-500/40 text-rose-300 text-sm flex items-center gap-3 shadow-lg">
                <i class="fa-solid fa-circle-exclamation text-rose-400 text-lg"></i>
                <div class="font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="p-4 text-center text-slate-500 text-xs border-t border-slate-800/60">
        <p class="font-medium">Powered by ValetFlow &bull; Secure Digital Valet Pass</p>
        <p class="text-[10px] mt-0.5 text-slate-600">Please present this pass to hotel staff upon vehicle collection</p>
    </footer>

    @stack('scripts')
</body>
</html>

