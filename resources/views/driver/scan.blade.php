@extends('layouts.driver')

@section('title', 'Scan Parking QR')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="text-center">
        <h2 class="text-xl font-bold text-white">Ticket QR Scanner</h2>
        <p class="text-xs text-slate-400 mt-1">Scan guest ticket card or enter ticket number manually</p>
    </div>

    <!-- Camera Scanner Box -->
    <div class="bg-slate-950 rounded-3xl border border-slate-800 p-4 space-y-4 shadow-xl">
        <div id="reader" class="w-full bg-slate-900 rounded-2xl overflow-hidden min-h-[260px] flex items-center justify-center text-xs text-slate-500">
            <div id="scan-placeholder" class="text-center p-6">
                <i class="fa-solid fa-camera text-3xl text-amber-500/70 mb-3 block"></i>
                <p class="text-slate-300 font-semibold mb-1">Camera Scanner Ready</p>
                <p class="text-[11px] text-slate-500">Tap button below to activate device camera</p>
            </div>
        </div>

        <button id="start-scan-btn" type="button" onclick="startScanner()" class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition shadow flex items-center justify-center gap-2">
            <i class="fa-solid fa-camera"></i> Activate Camera Scanner
        </button>
    </div>

    <!-- Manual Ticket Number Fallback Form -->
    <div class="bg-slate-950 rounded-3xl border border-slate-800 p-5 space-y-3">
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block text-center">
            Or Manual Ticket / Reg Lookup
        </span>

        <form action="{{ route('driver.lookup') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <input type="text" id="manual-query" name="query" required placeholder="Enter ticket number (e.g. VF-1001)"
                    class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono text-sm uppercase text-center focus:outline-none focus:border-amber-500">
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs uppercase tracking-wider transition">
                Find Ticket
            </button>
        </form>
    </div>

</div>

<!-- Scanner Script -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let html5QrCode = null;

    function startScanner() {
        document.getElementById('scan-placeholder').style.display = 'none';
        document.getElementById('start-scan-btn').style.display = 'none';

        html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 220, height: 220 } };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText, decodedResult) => {
                // Scanned successfully!
                html5QrCode.stop().then(() => {
                    // Submit to lookup
                    document.getElementById('manual-query').value = decodedText;
                    document.forms[1].submit();
                });
            },
            (errorMessage) => {
                // scan error ignored
            }
        ).catch(err => {
            alert("Camera error or permission denied: " + err);
            document.getElementById('scan-placeholder').style.display = 'block';
            document.getElementById('start-scan-btn').style.display = 'flex';
        });
    }
</script>
@endsection

