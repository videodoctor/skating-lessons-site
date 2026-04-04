<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ScheduleVerifyController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\WaitlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/sms-opt-in', function () {
    return view('sms-opt-in');
})->name('sms.optin');

Route::get('/player/grant-schaible', function () {
    return view('player-highlight');
})->name('player.grant');

Route::get('/rinks', function () {
    $rinks = \App\Models\Rink::where('is_displayed', true)->orderByRaw("FIELD(slug,'creve-coeur','kirkwood','webster-groves','brentwood','maryville')")->get();
    $todaySessions = \App\Models\RinkSession::with('rink')
        ->where('date', today())
        ->where('is_cancelled', false)
        ->orderBy('start_time')
        ->get()
        ->groupBy('rink_id');
    return view('rinks', compact('rinks', 'todaySessions'));
})->name('rinks');

// Waiver
Route::get('/waiver', [App\Http\Controllers\WaiverController::class, 'show'])->name('waiver.show');
Route::post('/waiver/sign', [App\Http\Controllers\WaiverController::class, 'sign'])->name('waiver.sign');

Route::get('/terms-and-conditions', function () {
    return view('terms');
})->name('terms');

Route::get('/privacy-policy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/', function () {
    $services = \App\Models\Service::where('is_active', true)->orderBy('price')->get();
    $comingSoonServices = \App\Models\Service::where('coming_soon', true)->orderBy('price')->get();
    $rinks = \App\Models\Rink::where('is_displayed', true)->orderByRaw("FIELD(slug,'creve-coeur','kirkwood','webster-groves','brentwood','maryville')")->get();

    // Hero media from admin config
    $heroMediaIds = json_decode(\App\Models\SiteSetting::get('homepage_hero_media', '[]'), true) ?: [];
    $heroMedia = $heroMediaIds ? \App\Models\StudentMedia::whereIn('id', $heroMediaIds)->get()->sortBy(fn($m) => array_search($m->id, $heroMediaIds))->values() : collect();

    // Bio photos from admin config
    $bioMediaIds = json_decode(\App\Models\SiteSetting::get('homepage_bio_media', '[]'), true) ?: [];
    $bioMedia = $bioMediaIds ? \App\Models\StudentMedia::whereIn('id', $bioMediaIds)->get()->sortBy(fn($m) => array_search($m->id, $bioMediaIds))->values() : collect();

    return view('home', compact('services', 'rinks', 'comingSoonServices', 'heroMedia', 'bioMedia'));
});

// Public Booking Flow
Route::get('/book', [BookingController::class, 'index'])->name('booking.index');
Route::post('/book/interest', [BookingController::class, 'submitInterest'])->name('booking.interest');
Route::get('/book/service/{service}', [BookingController::class, 'selectDate'])->name('booking.select-date');
Route::get('/book/service/{service}/date/{date}', [BookingController::class, 'selectTime'])->name('booking.select-time');
Route::get('/book/ajax/dates/{service}', [BookingController::class, 'ajaxDates'])->name('booking.ajax.dates');
Route::get('/book/ajax/slots/{service}/{date}', [BookingController::class, 'ajaxSlots'])->name('booking.ajax.slots');
Route::post('/book/submit', [BookingController::class, 'submit'])->name('booking.submit');
Route::get('/book/confirmation/{booking}', [BookingController::class, 'confirmation'])->name('booking.confirmation');
Route::get('/pay/{code}', [BookingController::class, 'pay'])->name('booking.pay');

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

    // Email verification (no auth required — link in email)
Route::get('/verify-email/{token}', [App\Http\Controllers\Auth\ClientAuthController::class, 'verifyEmail'])->name('client.verify-email');

// Guest booking conversion
Route::post('/booking/convert-guest', [App\Http\Controllers\BookingController::class, 'convertGuest'])->name('booking.convert-guest');

Route::get('/accept-terms', [\App\Http\Controllers\Auth\ClientAuthController::class, 'showAcceptTerms'])->name('client.accept-terms')->middleware('auth:client');
    Route::post('/accept-terms', [\App\Http\Controllers\Auth\ClientAuthController::class, 'acceptTerms'])->name('client.accept-terms.submit')->middleware('auth:client');

    Route::middleware('auth:client')->group(function () {
        Route::get('/dashboard', function () {
            $client = auth('client')->user();
            $bookings = $client->bookings()->with(['service', 'timeSlot.rink'])->latest()->get();
            \App\Services\ActivityLogger::log($client->id, 'view_dashboard', "{$client->full_name} viewed dashboard");
            return view('client.dashboard', compact('bookings'));
        })->name('client.dashboard');
        Route::get('/students/{student}', [App\Http\Controllers\ClientStudentController::class, 'show'])->name('client.student.show');
        Route::post('/students/{student}/upload', [App\Http\Controllers\ClientStudentController::class, 'upload'])->name('client.student.upload');
        Route::get('/verify-phone', [App\Http\Controllers\Auth\ClientAuthController::class, 'showVerifyPhone'])->name('client.verify-phone');
        Route::post('/verify-phone', [App\Http\Controllers\Auth\ClientAuthController::class, 'verifyPhone'])->name('client.verify-phone.submit');
        Route::post('/verify-phone/resend', [App\Http\Controllers\Auth\ClientAuthController::class, 'resendPhoneCode'])->name('client.verify-phone.resend');
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
    Route::post('/dashboard/prefs', [DashboardController::class, 'updatePrefs'])->name('admin.dashboard.prefs');

    // Bookings
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('admin.bookings.index');
    Route::post('/bookings/{booking}/approve', [AdminBookingController::class, 'approve'])->name('admin.bookings.approve');
    Route::post('/bookings/{booking}/reject', [AdminBookingController::class, 'reject'])->name('admin.bookings.reject');
    Route::post('/bookings/{booking}/link-client', [AdminBookingController::class, 'linkClient'])->name('admin.bookings.link-client');
    Route::post('/bookings/{booking}/cash-paid', [AdminBookingController::class, 'markCashPaid'])->name('admin.bookings.cash-paid');
    Route::post('/bookings/{booking}/venmo-paid', [AdminBookingController::class, 'markVenmoPaid'])->name('admin.bookings.venmo-paid');
    Route::get('/bookings/{booking}/edit', [AdminBookingController::class, 'edit'])->name('admin.bookings.edit');
    Route::patch('/bookings/{booking}', [AdminBookingController::class, 'update'])->name('admin.bookings.update');
    Route::get('/bookings/slots-for-rink-date', [AdminBookingController::class, 'slotsForRinkDate'])->name('admin.bookings.slots-for-rink-date');

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
    Route::post('/slots/block-range', [ScheduleController::class, 'blockDateRange'])->name('admin.slots.block-range');

    // Rinks
    Route::get('/rinks', [App\Http\Controllers\Admin\RinkController::class, 'index'])->name('admin.rinks.index');
    Route::patch('/rinks/{rink}', [App\Http\Controllers\Admin\RinkController::class, 'update'])->name('admin.rinks.update');

    // Clients
    Route::get('/clients', [ClientController::class, 'index'])->name('admin.clients.index');
    Route::post('/clients/link-student', [ClientController::class, 'linkStudent'])->name('admin.clients.link-student');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('admin.clients.show');
    Route::post('/clients', [ClientController::class, 'store'])->name('admin.clients.store');
    Route::patch('/clients/{client}', [ClientController::class, 'update'])->name('admin.clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('admin.clients.destroy');
    Route::post('/clients/{client}/students', [ClientController::class, 'addStudent'])->name('admin.clients.add-student');
    Route::patch('/clients/{client}/students/{student}', [ClientController::class, 'updateStudent'])->name('admin.clients.update-student');
    Route::delete('/clients/{client}/students/{student}', [ClientController::class, 'unlinkStudent'])->name('admin.clients.unlink-student');

    // Students
    // Calendar
    Route::get('/calendar', [App\Http\Controllers\Admin\CalendarController::class, 'index'])->name('admin.calendar');

    // Packages
    Route::get('/packages', [App\Http\Controllers\Admin\PackageController::class, 'index'])->name('admin.packages.index');
    Route::post('/packages', [App\Http\Controllers\Admin\PackageController::class, 'store'])->name('admin.packages.store');
    Route::patch('/packages/{package}', [App\Http\Controllers\Admin\PackageController::class, 'update'])->name('admin.packages.update');
    Route::patch('/packages/{package}/toggle', [App\Http\Controllers\Admin\PackageController::class, 'toggle'])->name('admin.packages.toggle');
    Route::get('/packages/{package}/waitlist', [App\Http\Controllers\Admin\PackageController::class, 'waitlist'])->name('admin.packages.waitlist');

    // Testimonials
    Route::get('/testimonials', [App\Http\Controllers\Admin\TestimonialController::class, 'index'])->name('admin.testimonials.index');
    Route::post('/testimonials', [App\Http\Controllers\Admin\TestimonialController::class, 'store'])->name('admin.testimonials.store');
    Route::patch('/testimonials/{testimonial}', [App\Http\Controllers\Admin\TestimonialController::class, 'update'])->name('admin.testimonials.update');
    Route::patch('/testimonials/{testimonial}/toggle', [App\Http\Controllers\Admin\TestimonialController::class, 'toggle'])->name('admin.testimonials.toggle');
    Route::delete('/testimonials/{testimonial}', [App\Http\Controllers\Admin\TestimonialController::class, 'destroy'])->name('admin.testimonials.destroy');

    // Venmo payments
    Route::get('/venmo', [App\Http\Controllers\Admin\VenmoAdminController::class, 'index'])->name('admin.venmo.index');
    Route::post('/venmo/parse-now', [App\Http\Controllers\Admin\VenmoAdminController::class, 'parseNow'])->name('admin.venmo.parse-now');
    Route::patch('/venmo/{payment}', [App\Http\Controllers\Admin\VenmoAdminController::class, 'link'])->name('admin.venmo.link');
    Route::patch('/venmo/{payment}/ignore', [App\Http\Controllers\Admin\VenmoAdminController::class, 'ignore'])->name('admin.venmo.ignore');
    Route::patch('/venmo/{payment}/unignore', [App\Http\Controllers\Admin\VenmoAdminController::class, 'unignore'])->name('admin.venmo.unignore');

    // Waivers
    Route::get('/waivers', [App\Http\Controllers\Admin\WaiverAdminController::class, 'index'])->name('admin.waivers.index');

    // Scraper dashboard
    Route::get('/scraper', [App\Http\Controllers\Admin\ScraperController::class, 'index'])->name('admin.scraper.index');
    Route::post('/scraper/run-all', [App\Http\Controllers\Admin\ScraperController::class, 'runAll'])->name('admin.scraper.run-all');
    Route::post('/scraper/run/{rinkSlug}', [App\Http\Controllers\Admin\ScraperController::class, 'runOne'])->name('admin.scraper.run-one');
    Route::patch('/scraper/settings/{rinkSlug}', [App\Http\Controllers\Admin\ScraperController::class, 'saveSettings'])->name('admin.scraper.save-settings');

    Route::get('/students', [App\Http\Controllers\Admin\StudentController::class, 'index'])->name('admin.students.index');
    Route::post('/students', [App\Http\Controllers\Admin\StudentController::class, 'store'])->name('admin.students.store');
    Route::patch('/students/{student}', [App\Http\Controllers\Admin\StudentController::class, 'update'])->name('admin.students.update');
    Route::delete('/students/{student}', [App\Http\Controllers\Admin\StudentController::class, 'destroy'])->name('admin.students.destroy');
    Route::post('/students/{student}/aliases', [App\Http\Controllers\Admin\StudentController::class, 'addAlias'])->name('admin.students.add-alias');

    // Student profiles & media
    Route::get('/students/{student}/profile', [App\Http\Controllers\Admin\StudentProfileController::class, 'show'])->name('admin.students.profile');
    Route::post('/students/{student}/upload', [App\Http\Controllers\Admin\StudentProfileController::class, 'upload'])->name('admin.students.upload');
    Route::post('/students/{student}/profile-photo/{media}', [App\Http\Controllers\Admin\StudentProfileController::class, 'setProfilePhoto'])->name('admin.students.set-profile-photo');
    Route::patch('/student-media/{media}/caption', [App\Http\Controllers\Admin\StudentProfileController::class, 'updateCaption'])->name('admin.students.update-caption');
    Route::patch('/student-media/{media}/reassign', [App\Http\Controllers\Admin\StudentProfileController::class, 'reassignMedia'])->name('admin.students.reassign-media');
    Route::post('/student-media/{media}/revert', [App\Http\Controllers\Admin\StudentProfileController::class, 'revertMedia'])->name('admin.students.revert-media');
    Route::delete('/student-media/{media}', [App\Http\Controllers\Admin\StudentProfileController::class, 'destroyMedia'])->name('admin.students.delete-media');
    Route::delete('/students/{student}/aliases/{alias}', [App\Http\Controllers\Admin\StudentController::class, 'removeAlias'])->name('admin.students.remove-alias');

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
    Route::post('/planner/scan/{scan}/rescan', [App\Http\Controllers\Admin\PlannerController::class, 'rescan'])->name('admin.planner.rescan');
    Route::delete('/planner/scan/{scan}', [App\Http\Controllers\Admin\PlannerController::class, 'destroy'])->name('admin.planner.destroy');
    Route::post('/planner/scan/{scan}/dismiss-missing/{booking}', [App\Http\Controllers\Admin\PlannerController::class, 'dismissMissing'])->name('admin.planner.dismiss-missing');
    Route::patch('/bookings/{booking}/cancel', [App\Http\Controllers\Admin\BookingController::class, 'cancel'])->name('admin.bookings.cancel');
    Route::post('/bookings/{booking}/suggest-time', [App\Http\Controllers\Admin\BookingController::class, 'suggestTime'])->name('admin.bookings.suggest-time');
    Route::get('/bookings/slots-for-date', [App\Http\Controllers\Admin\BookingController::class, 'slotsForDate'])->name('admin.bookings.slots-for-date');
    Route::post('/planner/create-booking', [App\Http\Controllers\Admin\PlannerController::class, 'createBooking'])->name('admin.planner.create-booking');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');
    Route::get('/analytics/activity', [AnalyticsController::class, 'activity'])->name('admin.analytics.activity');
    Route::get('/analytics/funnel', [AnalyticsController::class, 'funnel'])->name('admin.analytics.funnel');

    // Waitlist / Booking Pause
    Route::get('/waitlist', [WaitlistController::class, 'index'])->name('admin.waitlist.index');
    Route::post('/waitlist/toggle-pause', [WaitlistController::class, 'togglePause'])->name('admin.waitlist.toggle-pause');
    Route::delete('/waitlist/{interest}', [WaitlistController::class, 'destroy'])->name('admin.waitlist.destroy');

    // Impersonate client (start requires admin auth)
    Route::post('/impersonate/{client}', [\App\Http\Controllers\Admin\ImpersonateController::class, 'start'])->name('admin.impersonate.start');

    // Admin Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');

    // Media Gallery
    Route::get('/media', [App\Http\Controllers\Admin\MediaGalleryController::class, 'index'])->name('admin.media.index');
    Route::post('/media/upload', [App\Http\Controllers\Admin\MediaGalleryController::class, 'upload'])->name('admin.media.upload');
    Route::post('/media/presigned-url', [App\Http\Controllers\Admin\MediaUploadApiController::class, 'presignedUrl'])->name('admin.media.presigned');
    Route::post('/media/register', [App\Http\Controllers\Admin\MediaUploadApiController::class, 'register'])->name('admin.media.register');
    Route::post('/media/trim-video', [App\Http\Controllers\Admin\MediaUploadApiController::class, 'trimVideo'])->name('admin.media.trim-video');

    // Home Page Content
    Route::get('/home-page', [App\Http\Controllers\Admin\HomePageController::class, 'index'])->name('admin.homepage');
    Route::post('/home-page/hero-media', [App\Http\Controllers\Admin\HomePageController::class, 'updateHeroMedia'])->name('admin.homepage.update-hero');
    Route::post('/home-page/bio-media', [App\Http\Controllers\Admin\HomePageController::class, 'updateBioMedia'])->name('admin.homepage.update-bio');

    // Export / Reports
    Route::get('/export', [ExportController::class, 'index'])->name('admin.export');
    Route::get('/export/bookings', [ExportController::class, 'bookingsCsv'])->name('admin.export.bookings');
    Route::get('/export/clients', [ExportController::class, 'clientsCsv'])->name('admin.export.clients');
});

// Stop impersonation (outside admin auth — accessible while browsing as client)
Route::post('/admin/impersonate-stop', [\App\Http\Controllers\Admin\ImpersonateController::class, 'stop'])
    ->name('admin.impersonate.stop');

// Public calendar feeds
Route::get('/calendar/public-skating.ics', [\App\Http\Controllers\CalendarController::class, 'publicSessions']);
Route::get('/calendar/{rink}.ics', [\App\Http\Controllers\CalendarController::class, 'publicSessions']);
Route::get('/admin/calendar/bookings.ics', [\App\Http\Controllers\Admin\CalendarController::class, 'icalFeed'])->name('admin.calendar.ical');

// Waitlist join (public)
Route::post('/waitlist/{service}', function (\Illuminate\Http\Request $request, \App\Models\Service $service) {
    $request->validate(['email' => 'required|email', 'name' => 'nullable|string|max:100']);
    \App\Models\ServiceWaitlist::firstOrCreate(
        ['service_id' => $service->id, 'email' => $request->email],
        ['name' => $request->name]
    );
    return back()->with('waitlist_joined_' . $service->id, true);
})->name('waitlist.join');

// Booking suggestion accept/decline (public, token-protected)
Route::get('/booking/suggestion/{token}/accept', [App\Http\Controllers\Admin\BookingController::class, 'acceptSuggestion'])->name('booking.suggestion.accept');
Route::get('/booking/suggestion/{token}/decline', [App\Http\Controllers\Admin\BookingController::class, 'declineSuggestion'])->name('booking.suggestion.decline');

// Client lesson feed (token-protected, unique per client)
Route::get('/my/lessons.ics', [\App\Http\Controllers\ClientCalendarController::class, 'lessonFeed'])->name('client.calendar.ical');

require __DIR__.'/auth.php';

// Twilio inbound SMS webhook (exempt from CSRF)
Route::post('/webhooks/twilio/sms', [\App\Http\Controllers\TwilioWebhookController::class, 'inboundSms'])
    ->name('webhooks.twilio.sms');
