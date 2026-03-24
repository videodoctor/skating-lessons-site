<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\Rink;
use App\Models\Booking;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AdminBookingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'api.twilio.com/*' => Http::response(['sid' => 'SMtest'], 201),
        ]);

        // Simulate admin session (custom session-based auth)
        session(['admin_authenticated' => true]);
    }

    private function makeRink(array $overrides = []): Rink
    {
        return Rink::create(array_merge([
            'name'         => 'Test Rink',
            'slug'         => 'test-rink-' . uniqid(),
            'is_active'    => true,
            'schedule_url' => 'https://example.com/schedule',
        ], $overrides));
    }

    private function makeBooking(array $overrides = []): Booking
    {
        $uid = uniqid();
        $service = Service::create([
            'name' => 'Private Lesson', 'slug' => 'private-lesson-' . $uid,
            'description' => 'Lesson', 'price' => 55, 'duration_minutes' => 30,
            'is_active' => true, 'coming_soon' => false,
        ]);

        $rink = $this->makeRink();

        // Use a far-future date + unique time to avoid collisions with production data
        $testDate = Carbon::today()->addMonths(6)->addDays(random_int(1, 28))->toDateString();
        $hour = random_int(6, 20);
        $startTime = sprintf('%02d:%02d:00', $hour, 0);
        $endTime   = sprintf('%02d:%02d:00', $hour, 30);

        $slot = TimeSlot::create([
            'rink_id'      => $rink->id,
            'date'         => $testDate,
            'start_time'   => $startTime,
            'end_time'     => $endTime,
            'is_available' => false,
        ]);

        return Booking::create(array_merge([
            'service_id'          => $service->id,
            'time_slot_id'        => $slot->id,
            'client_name'         => 'Jane Smith',
            'client_email'        => 'jane@example.com',
            'client_phone'        => '',
            'status'              => 'pending',
            'price_paid'          => 55.00,
            'date'                => $testDate,
            'start_time'          => $startTime,
            'end_time'            => $endTime,
            'email_consent_at'    => now(),
            'guest_convert_token' => 'test-token-' . $uid,
        ], $overrides));
    }

    // ── Admin page access ────────────────────────────────────────────────

    public function test_admin_bookings_page_requires_session(): void
    {
        session()->forget('admin_authenticated');
        // Admin routes use standard Laravel auth middleware → redirects to /login
        $response = $this->get('/admin/bookings');
        $response->assertRedirect();
        $this->assertStringContainsString('login', $response->headers->get('Location'));
    }

    public function test_admin_bookings_page_loads_with_session(): void
    {
        $user = User::create([
            'name' => 'Admin', 'email' => 'admin-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
        ]);
        $response = $this->actingAs($user)->get('/admin/bookings');
        $response->assertStatus(200);
    }

    // ── Approve / Reject ─────────────────────────────────────────────────

    public function test_admin_can_approve_booking(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $booking = $this->makeBooking();
        $user = User::create(['name' => 'Admin', 'email' => 'a@t.com', 'password' => bcrypt('p')]);

        $response = $this->actingAs($user)->post("/admin/bookings/{$booking->id}/approve");
        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']);
    }

    public function test_admin_can_reject_booking(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $booking = $this->makeBooking();
        $user = User::create(['name' => 'Admin', 'email' => 'b@t.com', 'password' => bcrypt('p')]);

        $response = $this->actingAs($user)->post("/admin/bookings/{$booking->id}/reject");
        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'rejected']);
    }

    public function test_admin_can_cancel_booking(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $booking = $this->makeBooking(['status' => 'confirmed']);
        $user = User::create(['name' => 'Admin', 'email' => 'c@t.com', 'password' => bcrypt('p')]);

        $response = $this->actingAs($user)->patch("/admin/bookings/{$booking->id}/cancel");
        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    public function test_cancel_releases_time_slot(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $booking = $this->makeBooking(['status' => 'confirmed']);
        $slotId  = $booking->time_slot_id;
        $user = User::create(['name' => 'Admin', 'email' => 'd@t.com', 'password' => bcrypt('p')]);

        $this->actingAs($user)->patch("/admin/bookings/{$booking->id}/cancel");
        $this->assertDatabaseHas('time_slots', ['id' => $slotId, 'is_available' => true]);
    }

    // ── Suggest time ─────────────────────────────────────────────────────

    public function test_admin_can_suggest_new_time(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $booking = $this->makeBooking();
        $user    = User::create(['name' => 'Admin', 'email' => 'e@t.com', 'password' => bcrypt('p')]);

        $newSlot = TimeSlot::create([
            'rink_id'      => $booking->timeSlot->rink_id,
            'date'         => $booking->date,
            'start_time'   => '22:00:00',
            'end_time'     => '22:30:00',
            'is_available' => true,
        ]);

        $this->actingAs($user)->post("/admin/bookings/{$booking->id}/suggest-time", [
            'suggested_time_slot_id' => $newSlot->id,
            'suggestion_message'     => 'This time works better!',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id'                     => $booking->id,
            'status'                 => 'suggestion_pending',
            'suggested_time_slot_id' => $newSlot->id,
        ]);
    }

    public function test_client_can_accept_suggestion(): void
    {
        $booking = $this->makeBooking();
        $newSlot = TimeSlot::create([
            'rink_id'      => $booking->timeSlot->rink_id,
            'date'         => $booking->date,
            'start_time'   => '22:00:00',
            'end_time'     => '22:30:00',
            'is_available' => true,
        ]);

        $booking->update([
            'status'                 => 'suggestion_pending',
            'suggested_time_slot_id' => $newSlot->id,
            'suggestion_token'       => 'test-suggest-token',
        ]);

        $response = $this->get('/booking/suggestion/test-suggest-token/accept');
        $response->assertStatus(200);
        $response->assertSee('Confirmed');
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']);
        $this->assertDatabaseHas('time_slots', ['id' => $newSlot->id, 'is_available' => false]);
    }

    public function test_client_can_decline_suggestion(): void
    {
        $booking = $this->makeBooking();
        $newSlot = TimeSlot::create([
            'rink_id'      => $booking->timeSlot->rink_id,
            'date'         => $booking->date,
            'start_time'   => '22:00:00',
            'end_time'     => '22:30:00',
            'is_available' => true,
        ]);

        $booking->update([
            'status'                 => 'suggestion_pending',
            'suggested_time_slot_id' => $newSlot->id,
            'suggestion_token'       => 'test-decline-token',
        ]);

        $response = $this->get('/booking/suggestion/test-decline-token/decline');
        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'pending']);
    }

    public function test_suggestion_with_invalid_token_fails(): void
    {
        $response = $this->get('/booking/suggestion/bad-token/accept');
        $response->assertStatus(404);
    }
}
