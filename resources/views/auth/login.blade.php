<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - ValetFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-400 mx-auto flex items-center justify-center text-slate-950 font-black text-2xl shadow-xl shadow-amber-500/20 mb-3">
                VF
            </div>
            <h1 class="text-xl font-bold text-white tracking-wide">VALETFLOW</h1>
            <p class="text-xs text-amber-400 font-medium mt-0.5">Grand Azure Luxury Resort &bull; Staff Authentication</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-300 text-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Staff Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 pointer-events-none">
                        <i class="fa-solid fa-envelope text-sm"></i>
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 placeholder-slate-600"
                        placeholder="you@valetflow.test">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 pointer-events-none">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input type="password" id="password" name="password" required
                        class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 placeholder-slate-600"
                        placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center text-slate-400 select-none cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-amber-500 focus:ring-0 mr-2">
                    Remember me
                </label>
                <a href="{{ route('home') }}" class="text-amber-400 hover:text-amber-300">Return to Demo Hub</a>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm tracking-wider uppercase transition shadow-lg shadow-amber-500/20 active:scale-[0.98]">
                Sign In
            </button>
        </form>

        <!-- Fast Demo Credentials Quick Fill Buttons -->
        <div class="mt-8 pt-6 border-t border-slate-800/80">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-center mb-3">1-Click Demo Accounts</p>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button type="button" onclick="fillCreds('admin@valetflow.test', 'password')" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-left transition border border-slate-700/60">
                    <span class="font-bold block text-white">Hotel Admin</span>
                    <span class="text-[10px] text-slate-400 truncate block">admin@valetflow.test</span>
                </button>
                <button type="button" onclick="fillCreds('reception@valetflow.test', 'password')" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-left transition border border-slate-700/60">
                    <span class="font-bold block text-white">Reception Desk</span>
                    <span class="text-[10px] text-slate-400 truncate block">reception@valetflow.test</span>
                </button>
                <button type="button" onclick="fillCreds('driver1@valetflow.test', 'password')" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-left transition border border-slate-700/60">
                    <span class="font-bold block text-amber-400">Valet Driver</span>
                    <span class="text-[10px] text-slate-400 truncate block">driver1@valetflow.test</span>
                </button>
                <button type="button" onclick="fillCreds('dispatcher@valetflow.test', 'password')" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-left transition border border-slate-700/60">
                    <span class="font-bold block text-white">Dispatcher</span>
                    <span class="text-[10px] text-slate-400 truncate block">dispatcher@valetflow.test</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function fillCreds(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>

