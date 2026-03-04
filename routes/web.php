<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Homepage
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
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// Client Authentication
Route::prefix('client')->group(function () {
    Route::get('/register', [App\Http\Controllers\Auth\ClientAuthController::class, 'showRegister'])->name('client.register');
    Route::post('/register', [App\Http\Controllers\Auth\ClientAuthController::class, 'register']);
    Route::get('/login', [App\Http\Controllers\Auth\ClientAuthController::class, 'showLogin'])->name('client.login');
    Route::post('/login', [App\Http\Controllers\Auth\ClientAuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\Auth\ClientAuthController::class, 'logout'])->name('client.logout');
    
    // Protected client routes
    Route::middleware('auth:client')->group(function () {
        Route::get('/dashboard', function() {
            $bookings = Auth::guard('client')->user()->bookings()->with(['service', 'timeSlot.rink'])->latest()->get();
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('admin.bookings.index');
    Route::post('/bookings/{booking}/approve', [AdminBookingController::class, 'approve'])->name('admin.bookings.approve');
    Route::post('/bookings/{booking}/reject', [AdminBookingController::class, 'reject'])->name('admin.bookings.reject');
});

require __DIR__.'/auth.php';

// Public calendar feeds
Route::get('/calendar/public-skating.ics', [App\Http\Controllers\CalendarController::class, 'publicSessions']);
Route::get('/calendar/{rink}.ics', [App\Http\Controllers\CalendarController::class, 'publicSessions']);
