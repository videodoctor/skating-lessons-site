<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;

class ClientAuthTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Fake Twilio so registration never hits real SMS API
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true], 200),
            'api.twilio.com/*'             => Http::response(['sid' => 'SMtest'], 201),
        ]);
    }

    private function makeClient(array $attrs = []): Client
    {
        return Client::create(array_merge([
            'first_name' => 'Jane',
            'last_name'  => 'Smith',
            'email'      => 'jane@example.com',
            'phone'      => '',
            'password'   => bcrypt('password123'),
        ], $attrs));
    }

    // ── Registration ─────────────────────────────────────────────────────

    public function test_registration_page_loads(): void
    {
        $this->get('/client/register')->assertStatus(200);
    }

    public function test_client_can_register(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->post('/client/register', [
            'first_name'            => 'Jane',
            'last_name'             => 'Smith',
            'email'                 => 'jane@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'email_consent'         => '1',
            'waiver_accepted'       => '1',
            'terms_accepted'        => '1',
            'cf-turnstile-response' => 'test',
        ]);

        $this->assertDatabaseHas('clients', ['email' => 'jane@example.com']);
    }

    public function test_registration_requires_first_name(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/client/register', [
            'email'                 => 'jane@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'email_consent'         => '1',
            'waiver_accepted'       => '1',
            'terms_accepted'        => '1',
            'cf-turnstile-response' => 'test',
        ]);

        $response->assertSessionHasErrors('first_name');
    }

    public function test_registration_requires_email(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/client/register', [
            'first_name'            => 'Jane',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'email_consent'         => '1',
            'waiver_accepted'       => '1',
            'terms_accepted'        => '1',
            'cf-turnstile-response' => 'test',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/client/register', [
            'first_name'            => 'Jane',
            'email'                 => 'jane@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'wrong',
            'email_consent'         => '1',
            'waiver_accepted'       => '1',
            'terms_accepted'        => '1',
            'cf-turnstile-response' => 'test',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_duplicate_email_rejected(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->makeClient();

        $response = $this->post('/client/register', [
            'first_name'            => 'Jane',
            'last_name'             => 'Smith',
            'email'                 => 'jane@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'email_consent'         => '1',
            'waiver_accepted'       => '1',
            'terms_accepted'        => '1',
            'cf-turnstile-response' => 'test',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_new_client_gets_calendar_token(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->post('/client/register', [
            'first_name'            => 'Jane',
            'last_name'             => 'Smith',
            'email'                 => 'jane@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'email_consent'         => '1',
            'waiver_accepted'       => '1',
            'terms_accepted'        => '1',
            'cf-turnstile-response' => 'test',
        ]);

        $client = Client::where('email', 'jane@example.com')->first();
        $this->assertNotNull($client?->calendar_token);
    }

    // ── Login ────────────────────────────────────────────────────────────

    public function test_login_page_loads(): void
    {
        $this->get('/client/login')->assertStatus(200);
    }

    public function test_client_can_login(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->makeClient();

        $response = $this->post('/client/login', [
            'email'    => 'jane@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
    }

    public function test_wrong_password_rejected(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->makeClient();

        $response = $this->post('/client/login', [
            'email'    => 'jane@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
    }

    // ── iCal feed ────────────────────────────────────────────────────────

    public function test_ical_feed_requires_valid_token(): void
    {
        $response = $this->get('/my/lessons.ics?token=badtoken');
        $response->assertStatus(403);
    }

    public function test_ical_feed_returns_calendar_with_valid_token(): void
    {
        $this->makeClient([
            'email'          => 'ical@example.com',
            'calendar_token' => 'valid-test-token-abc123',
        ]);

        $response = $this->get('/my/lessons.ics?token=valid-test-token-abc123');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
    }
}
