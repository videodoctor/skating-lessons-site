<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\Rink;
use App\Models\Booking;
use App\Models\Client;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class BookingTest extends TestCase
{
    use DatabaseTransactions;

    private Service $service;
    private Rink $rink;
    private TimeSlot $slot;
    private string $testDate;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true], 200),
            'api.twilio.com/*'             => Http::response(['sid' => 'SMtest'], 201),
        ]);

        // Ensure booking is not paused for tests
        SiteSetting::set('booking_paused', '0');

        $uid = uniqid();
        $this->service = Service::create([
            'name'             => 'Test Private Lesson',
            'slug'             => 'test-private-lesson-' . $uid,
            'description'      => 'One-on-one skating lesson',
            'price'            => 55.00,
            'duration_minutes' => 30,
            'features'         => ['Edge work', 'Power skating'],
            'is_active'        => true,
            'coming_soon'      => false,
            'show_price'       => true,
            'show_duration'    => true,
            'show_features'    => true,
            'show_description' => true,
        ]);

        $this->rink = Rink::create([
            'name'         => 'Test Rink',
            'slug'         => 'test-rink-' . $uid,
            'is_active'    => true,
            'schedule_url' => 'https://example.com/schedule',
        ]);

        $this->testDate = Carbon::today()->addDays(45)->toDateString();
        $this->slot = TimeSlot::create([
            'rink_id'      => $this->rink->id,
            'date'         => $this->testDate,
            'start_time'   => '14:00:00',
            'end_time'     => '14:30:00',
            'is_available' => true,
        ]);
    }

    public function test_booking_page_loads(): void
    {
        $response = $this->get('/book');
        $response->assertStatus(200);
        $response->assertSee('Test Private Lesson');
    }

    public function test_booking_page_shows_active_services_only(): void
    {
        Service::create([
            'name' => 'Hidden Service', 'slug' => 'hidden',
            'description' => 'Hidden', 'price' => 100, 'duration_minutes' => 60,
            'is_active' => false, 'coming_soon' => false,
        ]);
        $response = $this->get('/book');
        $response->assertSee('Test Private Lesson');
        $response->assertDontSee('Hidden Service');
    }

    public function test_booking_page_shows_coming_soon_services(): void
    {
        Service::create([
            'name' => 'Assessment Package', 'slug' => 'assessment',
            'description' => 'Full assessment', 'price' => 120, 'duration_minutes' => 60,
            'is_active' => false, 'coming_soon' => true, 'show_price' => true,
        ]);
        $response = $this->get('/book');
        $response->assertSee('Assessment Package');
        $response->assertSee('Coming Soon');
    }

    public function test_ajax_dates_returns_available_dates(): void
    {
        $response = $this->getJson("/book/ajax/dates/{$this->service->id}");
        $response->assertStatus(200);
        $response->assertJsonFragment([Carbon::today()->addDays(45)->toDateString()]);
    }

    public function test_ajax_dates_excludes_past_dates(): void
    {
        TimeSlot::create([
            'rink_id'      => $this->rink->id,
            'date'         => Carbon::today()->subMonths(6)->toDateString(),
            'start_time'   => '10:00:00',
            'end_time'     => '10:30:00',
            'is_available' => true,
        ]);
        $response = $this->getJson("/book/ajax/dates/{$this->service->id}");
        $this->assertNotContains(Carbon::today()->subMonths(6)->toDateString(), $response->json());
    }

    public function test_ajax_slots_returns_slots_for_date(): void
    {
        $response = $this->getJson("/book/ajax/slots/{$this->service->id}/{$this->testDate}");
        $response->assertStatus(200);
        $response->assertJsonFragment(['rink' => 'Test Rink']);
    }

    public function test_ajax_slots_excludes_unavailable_slots(): void
    {
        $this->slot->update(['is_available' => false]);
        $response = $this->getJson("/book/ajax/slots/{$this->service->id}/{$this->testDate}");
        $this->assertEmpty($response->json());
    }

    public function test_ajax_slots_excludes_inactive_rinks(): void
    {
        $this->rink->update(['is_bookable' => false]);
        $response = $this->getJson("/book/ajax/slots/{$this->service->id}/{$this->testDate}");
        $this->assertEmpty($response->json());
    }

    public function test_guest_can_submit_booking(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $response = $this->post('/book/submit', [
            'service_id'            => $this->service->id,
            'time_slot_id'          => $this->slot->id,
            'client_name'           => 'Jane Smith',
            'client_email'          => 'jane@example.com',
            'client_phone'          => '3145550100',
            'student_name'          => 'Little Jane',
            'student_age'           => 8,
            'skill_level'           => 'beginner',
            'email_consent'         => '1',
            'cancellation_policy'   => '1',
            'guest_sms_consent'     => '0',
            'cf-turnstile-response' => 'test',
        ]);
        $this->assertDatabaseHas('bookings', ['client_email' => 'jane@example.com', 'status' => 'pending']);
        $this->assertDatabaseHas('time_slots', ['id' => $this->slot->id, 'is_available' => false]);
    }

    public function test_booking_requires_email_consent(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $response = $this->post('/book/submit', [
            'service_id'          => $this->service->id,
            'time_slot_id'        => $this->slot->id,
            'client_name'         => 'Jane Smith',
            'client_email'        => 'jane@example.com',
            'cancellation_policy' => '1',
        ]);
        $response->assertSessionHasErrors('email_consent');
    }

    public function test_booking_requires_cancellation_policy(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $response = $this->post('/book/submit', [
            'service_id'    => $this->service->id,
            'time_slot_id'  => $this->slot->id,
            'client_name'   => 'Jane Smith',
            'client_email'  => 'jane@example.com',
            'email_consent' => '1',
        ]);
        $response->assertSessionHasErrors('cancellation_policy');
    }

    public function test_double_booking_same_slot_fails(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $payload = [
            'service_id'            => $this->service->id,
            'time_slot_id'          => $this->slot->id,
            'client_name'           => 'Jane Smith',
            'client_email'          => 'jane@example.com',
            'client_phone'          => '3145550100',
            'student_name'          => 'Little Jane',
            'student_age'           => 8,
            'skill_level'           => 'beginner',
            'email_consent'         => '1',
            'cancellation_policy'   => '1',
            'guest_sms_consent'     => '0',
            'cf-turnstile-response' => 'test',
        ];
        $first = $this->post('/book/submit', $payload);
        $this->assertDatabaseHas('bookings', ['client_email' => 'jane@example.com']);
        $response = $this->post('/book/submit', array_merge($payload, ['client_email' => 'bob@example.com']));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('bookings', ['client_email' => 'bob@example.com']);
    }

    public function test_booking_invalid_service_rejected(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $response = $this->post('/book/submit', [
            'service_id'          => 99999,
            'time_slot_id'        => $this->slot->id,
            'client_name'         => 'Jane Smith',
            'client_email'        => 'jane@example.com',
            'email_consent'       => '1',
            'cancellation_policy' => '1',
        ]);
        $response->assertSessionHasErrors('service_id');
    }
}
