<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Guest QR Pass - Ticket #{{ $ticket->ticket_number }}</title>
    @vite(['resources/css/app.css'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            .print-card { box-shadow: none !important; border: 1.5px solid #000 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans p-6 min-h-screen flex flex-col items-center justify-center">

    <!-- Action Toolbar (hidden on print) -->
    <div class="no-print mb-6 flex items-center gap-3">
        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs uppercase tracking-wider shadow flex items-center gap-2">
            <i class="fa-solid fa-print"></i> Print QR Pass Now
        </button>
        <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left"></i> View Ticket Detail
        </a>
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition">
            Operations Desk
        </a>
    </div>

    <!-- Printable Guest Valet Card (Thermal / Cardstock Proportion) -->
    <div class="print-card w-full max-w-sm bg-white rounded-3xl border-2 border-slate-900 p-8 shadow-xl text-center space-y-5">

        <!-- Hotel Branding -->
        <div class="border-b-2 border-dashed border-slate-200 pb-4">
            <p class="text-[10px] tracking-widest font-black uppercase text-amber-600">OFFICIAL VALET PASS</p>
            <h1 class="text-lg font-black text-slate-950 tracking-wide mt-0.5">{{ $ticket->hotel->name }}</h1>
            <p class="text-[11px] text-slate-500">Luxury Valet Parking Service</p>
        </div>

        <!-- Ticket Number Hero -->
        <div class="bg-slate-950 text-white rounded-2xl py-3 px-4">
            <span class="text-[10px] uppercase tracking-widest text-slate-400 block">TICKET NUMBER</span>
            <span class="text-3xl font-black font-mono tracking-wider text-amber-400">{{ $ticket->ticket_number }}</span>
        </div>

        <!-- High-Contrast QR Code -->
        <div class="flex justify-center p-2 bg-white rounded-2xl border border-slate-200">
            {!! $qrSvg !!}
        </div>

        <!-- Simple Guest Scan Instructions -->
        <div class="space-y-1">
            <p class="text-xs font-black text-slate-950 uppercase tracking-wide">Scan to Request Your Vehicle</p>
            <p class="text-[11px] text-slate-600 leading-snug">
                Point your phone camera at this QR code anytime to view status and choose 5, 10, or 15-minute pickup.
            </p>
        </div>

        <!-- Vehicle Details (for staff visual matching) -->
        <div class="pt-3 border-t-2 border-dashed border-slate-200 text-left text-xs space-y-1">
            <div class="flex justify-between">
                <span class="text-slate-500">Vehicle:</span>
                <span class="font-bold text-slate-900 font-mono">{{ $ticket->vehicle->registration_number }}</span>
            </div>
            <div class="flex justify-between text-[11px]">
                <span class="text-slate-500">Model:</span>
                <span class="text-slate-700">{{ $ticket->vehicle->color }} {{ $ticket->vehicle->make }} {{ $ticket->vehicle->model }}</span>
            </div>
            <div class="flex justify-between text-[10px] text-slate-400 pt-1">
                <span>Issued:</span>
                <span>{{ $ticket->checked_in_at->format('M d, Y H:i') }}</span>
            </div>
        </div>

        <p class="text-[9px] text-slate-400 uppercase tracking-wider pt-2 border-t border-slate-100">
            Keep this card with you until vehicle is returned &bull; ValetFlow
        </p>

    </div>

</body>
</html>

