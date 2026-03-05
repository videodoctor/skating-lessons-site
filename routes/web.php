<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ScheduleController;
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
    return view('rinks', compact('rinks'));
})->name('rinks');

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

    // Schedule & Slots
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('admin.schedule');
    Route::post('/slots', [ScheduleController::class, 'storeSlot'])->name('admin.slots.store');
    Route::delete('/slots/{timeSlot}', [ScheduleController::class, 'destroySlot'])->name('admin.slots.destroy');
    Route::post('/slots/block-day', [ScheduleController::class, 'blockDay'])->name('admin.slots.block-day');

    // Clients
    Route::get('/clients', [ClientController::class, 'index'])->name('admin.clients.index');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('admin.clients.show');

    // Planner OCR
    Route::get('/planner-ocr', [ScheduleController::class, 'plannerOcr'])->name('admin.planner-ocr');

    // Export / Reports
    Route::get('/export', [ExportController::class, 'index'])->name('admin.export');
    Route::get('/export/bookings', [ExportController::class, 'bookingsCsv'])->name('admin.export.bookings');
    Route::get('/export/clients', [ExportController::class, 'clientsCsv'])->name('admin.export.clients');
});

// Public calendar feeds
Route::get('/calendar/public-skating.ics', [\App\Http\Controllers\CalendarController::class, 'publicSessions']);
Route::get('/calendar/{rink}.ics', [\App\Http\Controllers\CalendarController::class, 'publicSessions']);

require __DIR__.'/auth.php';
