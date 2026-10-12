<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DriverManagementController;
use App\Http\Controllers\GuestTicketController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use App\Models\ParkingTicket;
use App\Models\QrToken;
use Illuminate\Support\Facades\Route;

// Home / Demo Hub
Route::get('/', function () {
    $activeTicket = ParkingTicket::with(['vehicle', 'hotel'])->where('status', 'PARKED')->first();
    $qrToken = null;
    if ($activeTicket) {
        $tokenRecord = QrToken::where('ticket_id', $activeTicket->id)->whereNull('revoked_at')->latest()->first();
        // If hashed, we can generate a demo token or direct link
    }

    return view('welcome', [
        'sampleTicket' => $activeTicket,
    ]);
})->name('home');

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Guest Routes
Route::prefix('guest')->name('guest.')->group(function () {
    Route::get('/{token}', [GuestTicketController::class, 'show'])->name('show');
    Route::post('/{token}/retrieval-requests', [GuestTicketController::class, 'storeRequest'])->name('retrieval-request')->middleware('throttle:15,1');
    Route::get('/{token}/request-status', [GuestTicketController::class, 'status'])->name('status')->middleware('throttle:60,1');
});

// Driver Routes (Authenticated staff)
Route::middleware(['auth', 'active'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/dashboard', [DriverController::class, 'dashboard'])->name('dashboard');
    Route::get('/scan', [DriverController::class, 'scan'])->name('scan');
    Route::post('/lookup', [DriverController::class, 'lookup'])->name('lookup');
    Route::get('/tickets/{ticket}', [DriverController::class, 'showTicket'])->name('tickets.show');
    Route::post('/tickets/{ticket}/start-parking', [DriverController::class, 'startParking'])->name('tickets.start-parking');
    Route::post('/tickets/{ticket}/mark-parked', [DriverController::class, 'markParked'])->name('tickets.mark-parked');
    Route::post('/requests/{request}/accept', [DriverController::class, 'acceptRequest'])->name('requests.accept');
    Route::post('/requests/{request}/start', [DriverController::class, 'startRetrieval'])->name('requests.start');
    Route::post('/requests/{request}/ready', [DriverController::class, 'readyAtGate'])->name('requests.ready');
    Route::post('/requests/{retrievalRequest}/complete', [DriverController::class, 'completeHandover'])->name('requests.complete');
    Route::get('/history', [DriverController::class, 'history'])->name('history');
});

// Admin, Reception, Manager & Dispatcher Routes
Route::middleware(['auth', 'active', 'role:super_admin,hotel_admin,manager,receptionist,dispatcher'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Tickets
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/assign-driver', [TicketController::class, 'assignDriver'])->name('tickets.assign-driver');
    Route::post('/tickets/{ticket}/reissue-qr', [TicketController::class, 'reissueQr'])->name('tickets.reissue-qr');
    Route::get('/qr-cards/{ticket}/print', [TicketController::class, 'printQr'])->name('tickets.print-qr');

    // Driver Management
    Route::get('/drivers', [DriverManagementController::class, 'index'])->name('drivers.index');
    Route::post('/drivers', [DriverManagementController::class, 'store'])->name('drivers.store');
    Route::post('/drivers/{driver}/toggle', [DriverManagementController::class, 'toggleActive'])->name('drivers.toggle');

    // Parking Locations
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
    Route::post('/locations/{location}/toggle', [LocationController::class, 'toggleActive'])->name('locations.toggle');

    // Reports & Logs
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});
