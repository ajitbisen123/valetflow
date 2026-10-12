<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ValetFlow') - Luxury Hotel Valet Operations</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col md:flex-row">

    <!-- Mobile Top Header -->
    <header class="md:hidden bg-slate-900 text-white px-4 py-3 flex items-center justify-between border-b border-slate-800">
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center text-slate-950 font-bold text-lg">V</span>
            <span class="font-bold text-lg tracking-wide">VALETFLOW</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-slate-800 px-2 py-1 rounded text-amber-400 font-medium">{{ Auth::user()->role->label() }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-slate-400 hover:text-white text-sm"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
            </form>
        </div>
    </header>

    <!-- Sidebar Navigation for Desktop -->
    <aside class="w-full md:w-64 bg-slate-900 text-slate-300 flex-shrink-0 flex flex-col justify-between p-4 border-r border-slate-800">
        <div>
            <!-- Hotel & App Branding -->
            <div class="px-3 py-4 flex items-center gap-3 border-b border-slate-800 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 to-amber-400 flex items-center justify-center text-slate-950 font-black text-xl shadow-lg shadow-amber-500/20">
                    VF
                </div>
                <div>
                    <h1 class="text-white font-bold tracking-wider text-base leading-tight">VALETFLOW</h1>
                    <p class="text-xs text-amber-400 font-medium truncate max-w-[140px]">{{ Auth::user()->hotel->name ?? 'Grand Azure Resort' }}</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1 text-sm font-medium">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500 text-slate-950 font-semibold shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-gauge-high w-5 text-center"></i>
                    <span>Operations Dashboard</span>
                </a>

                <a href="{{ route('admin.tickets.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.tickets.create') ? 'bg-amber-500 text-slate-950 font-semibold shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-car-side w-5 text-center"></i>
                    <span>New Check-In</span>
                </a>

                <a href="{{ route('admin.tickets.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.tickets.index*') && !request()->routeIs('admin.tickets.create') ? 'bg-amber-500 text-slate-950 font-semibold shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-ticket w-5 text-center"></i>
                    <span>Ticket Registry</span>
                </a>

                <a href="{{ route('admin.drivers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.drivers.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-id-badge w-5 text-center"></i>
                    <span>Drivers & Staff</span>
                </a>

                <a href="{{ route('admin.locations.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.locations.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-square-parking w-5 text-center"></i>
                    <span>Parking Bays</span>
                </a>

                <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.reports.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-line w-5 text-center"></i>
                    <span>Analytics & SLAs</span>
                </a>

                <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.audit-logs.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-shield-halved w-5 text-center"></i>
                    <span>Audit Trail</span>
                </a>

                <div class="pt-4 mt-4 border-t border-slate-800">
                    <a href="{{ route('driver.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs text-amber-300/80 hover:bg-slate-800 hover:text-amber-300">
                        <i class="fa-solid fa-mobile-screen w-5 text-center"></i>
                        <span>Switch to Driver View</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- User Profile Card -->
        <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/60 mt-6">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center font-bold text-amber-400 text-sm">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-white truncate max-w-[110px]">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider">{{ Auth::user()->role->label() }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" class="text-slate-400 hover:text-rose-400 transition p-1 text-sm">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-700/40">
                <span>Timezone: {{ Auth::user()->hotel->timezone ?? 'UTC' }}</span>
                <span class="text-emerald-400 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Live</span>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 p-4 md:p-8 max-w-7xl w-full mx-auto overflow-y-auto">
        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <div class="font-medium text-sm">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                <div class="font-medium text-sm">{{ session('error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
                <div class="font-semibold text-sm mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Please resolve the following errors:
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 ml-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>

