<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ScheduleVerifyController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/rinks', function () {
    $rinks = \App\Models\Rink::orderByRaw("FIELD(slug,'creve-coeur','kirkwood','webster-groves','brentwood','maryville')")->get();
    $todaySessions = \App\Models\RinkSession::with('rink')
        ->where('date', today())
        ->where('is_cancelled', false)
        ->orderBy('start_time')
        ->get()
        ->groupBy('rink_id');
    return view('rinks', compact('rinks', 'todaySessions'));
})->name('rinks');

Route::get('/terms-and-conditions', function () {
    return view('terms');
})->name('terms');

Route::get('/privacy-policy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/', function () {
    $services = \App\Models\Service::where('is_active', true)->orderBy('price')->get();
    return view('home', compact('services'));
});

// Public Booking Flow
Route::get('/book', [BookingController::class, 'index'])->name('booking.index');
Route::get('/book/service/{service}', [BookingController::class, 'selectDate'])->name('booking.select-date');
Route::get('/book/service/{service}/date/{date}', [BookingController::class, 'selectTime'])->name('booking.select-time');
Route::post('/book/submit', [BookingController::class, 'submit'])->name('booking.submit');
Route::get('/book/confirmation/{booking}', [BookingController::class, 'confirmation'])->name('booking.confirmation');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => redirect()->route('admin.dashboard'))->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Client Auth
|--------------------------------------------------------------------------
*/
Route::prefix('client')->group(function () {
    Route::get('/register', [\App\Http\Controllers\Auth\ClientAuthController::class, 'showRegister'])->name('client.register');
    Route::post('/register', [\App\Http\Controllers\Auth\ClientAuthController::class, 'register']);
    Route::get('/login', [\App\Http\Controllers\Auth\ClientAuthController::class, 'showLogin'])->name('client.login');
    Route::post('/login', [\App\Http\Controllers\Auth\ClientAuthController::class, 'login']);
    Route::post('/logout', [\App\Http\Controllers\Auth\ClientAuthController::class, 'logout'])->name('client.logout');

    Route::middleware('auth:client')->group(function () {
        Route::get('/dashboard', function () {
            $bookings = auth('client')->user()->bookings()->with(['service', 'timeSlot.rink'])->latest()->get();
            return view('client.dashboard', compact('bookings'));
        })->name('client.dashboard');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Bookings
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('admin.bookings.index');
    Route::post('/bookings/{booking}/approve', [AdminBookingController::class, 'approve'])->name('admin.bookings.approve');
    Route::post('/bookings/{booking}/reject', [AdminBookingController::class, 'reject'])->name('admin.bookings.reject');
    Route::post('/bookings/{booking}/link-client', [AdminBookingController::class, 'linkClient'])->name('admin.bookings.link-client');
    Route::post('/bookings/{booking}/cash-paid', [AdminBookingController::class, 'markCashPaid'])->name('admin.bookings.cash-paid');
    Route::post('/bookings/{booking}/venmo-paid', [AdminBookingController::class, 'markVenmoPaid'])->name('admin.bookings.venmo-paid');

    // Schedule & Slots
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('admin.schedule');
    Route::get('/schedule/verify', [ScheduleVerifyController::class, 'index'])->name('admin.schedule.verify');
    Route::get('/schedule/source/{run}', [ScheduleVerifyController::class, 'serveSource'])->name('admin.schedule.source');
    Route::post('/schedule/rescrape', [ScheduleVerifyController::class, 'rescrape'])->name('admin.schedule.rescrape');
    Route::post('/schedule/session', [ScheduleVerifyController::class, 'storeSession'])->name('admin.schedule.session.store');
    Route::patch('/schedule/session/{id}', [ScheduleVerifyController::class, 'updateSession'])->name('admin.schedule.session.update');
    Route::delete('/schedule/session/{id}', [ScheduleVerifyController::class, 'destroySession'])->name('admin.schedule.session.destroy');
    Route::post('/slots', [ScheduleController::class, 'storeSlot'])->name('admin.slots.store');
    Route::delete('/slots/{timeSlot}', [ScheduleController::class, 'destroySlot'])->name('admin.slots.destroy');
    Route::post('/slots/block-day', [ScheduleController::class, 'blockDay'])->name('admin.slots.block-day');

    // Clients
    Route::get('/clients', [ClientController::class, 'index'])->name('admin.clients.index');
    Route::post('/clients/link-student', [ClientController::class, 'linkStudent'])->name('admin.clients.link-student');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('admin.clients.show');
    Route::post('/clients', [ClientController::class, 'store'])->name('admin.clients.store');
    Route::patch('/clients/{client}', [ClientController::class, 'update'])->name('admin.clients.update');

    // Planner OCR (legacy stub)
    Route::get('/planner-ocr', [ScheduleController::class, 'plannerOcr'])->name('admin.planner-ocr');

    // Planner
    Route::get('/planner', [App\Http\Controllers\Admin\PlannerController::class, 'index'])->name('admin.planner');
    Route::post('/planner/analyze', [App\Http\Controllers\Admin\PlannerController::class, 'analyze'])->name('admin.planner.analyze');
    Route::get('/planner/scan/{scan}', [App\Http\Controllers\Admin\PlannerController::class, 'show'])->name('admin.planner.scan');
    Route::patch('/planner/entry/{entry}', [App\Http\Controllers\Admin\PlannerController::class, 'updateEntry'])->name('admin.planner.entry.update');
    Route::post('/planner/entry/{entry}/confirm', [App\Http\Controllers\Admin\PlannerController::class, 'confirmEntry'])->name('admin.planner.entry.confirm');
    Route::post('/planner/entry/{entry}/ignore', [App\Http\Controllers\Admin\PlannerController::class, 'ignoreEntry'])->name('admin.planner.entry.ignore');
    Route::post('/planner/entry/{entry}/unignore', [App\Http\Controllers\Admin\PlannerController::class, 'unignoreEntry'])->name('admin.planner.entry.unignore');
    Route::post('/planner/create-student', [App\Http\Controllers\Admin\PlannerController::class, 'createStudent'])->name('admin.planner.create-student');
    Route::post('/planner/add-alias', [App\Http\Controllers\Admin\PlannerController::class, 'addAlias'])->name('admin.planner.add-alias');
    Route::post('/planner/scan/{scan}/finalize', [App\Http\Controllers\Admin\PlannerController::class, 'finalize'])->name('admin.planner.finalize');
    Route::post('/planner/create-booking', [App\Http\Controllers\Admin\PlannerController::class, 'createBooking'])->name('admin.planner.create-booking');

    // Export / Reports
    Route::get('/export', [ExportController::class, 'index'])->name('admin.export');
    Route::get('/export/bookings', [ExportController::class, 'bookingsCsv'])->name('admin.export.bookings');
    Route::get('/export/clients', [ExportController::class, 'clientsCsv'])->name('admin.export.clients');
});

// Public calendar feeds
Route::get('/calendar/public-skating.ics', [\App\Http\Controllers\CalendarController::class, 'publicSessions']);
Route::get('/calendar/{rink}.ics', [\App\Http\Controllers\CalendarController::class, 'publicSessions']);

require __DIR__.'/auth.php';

// Twilio inbound SMS webhook (exempt from CSRF)
Route::post('/webhooks/twilio/sms', [\App\Http\Controllers\TwilioWebhookController::class, 'inboundSms'])
    ->name('webhooks.twilio.sms');
