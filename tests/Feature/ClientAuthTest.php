<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientAuthTest extends TestCase
{
    use RefreshDatabase;

    // ── Registration ─────────────────────────────────────────────────────

    public function test_registration_page_loads(): void
    {
        $this->get('/client/register')->assertStatus(200);
    }

    public function test_client_can_register(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/client/register', [
            'first_name' => 'Jane',
            'last_name'  => 'Smith',
            'email'      => 'jane@example.com',
            'password'   => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('clients', ['email' => 'jane@example.com']);
    }

    public function test_registration_requires_email(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/client/register', [
            'first_name' => 'Jane',
            'last_name'  => 'Smith',
            'password'   => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/client/register', [
            'first_name' => 'Jane',
            'last_name'  => 'Smith',
            'email'      => 'jane@example.com',
            'password'   => 'password123',
            'password_confirmation' => 'wrong',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_duplicate_email_rejected(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        Client::create([
            'first_name' => 'Existing', 'last_name' => 'User',
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/client/register', [
            'first_name' => 'Jane', 'last_name' => 'Smith',
            'email'      => 'jane@example.com',
            'password'   => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_new_client_gets_calendar_token(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->post('/client/register', [
            'first_name' => 'Jane', 'last_name' => 'Smith',
            'email'      => 'jane@example.com',
            'password'   => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $client = Client::where('email', 'jane@example.com')->first();
        $this->assertNotNull($client?->calendar_token);
        $this->assertGreaterThanOrEqual(24, strlen($client->calendar_token));
    }

    // ── Login ────────────────────────────────────────────────────────────

    public function test_login_page_loads(): void
    {
        $this->get('/client/login')->assertStatus(200);
    }

    public function test_client_can_login(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        Client::create([
            'first_name' => 'Jane', 'last_name' => 'Smith',
            'email'      => 'jane@example.com',
            'password'   => bcrypt('password123'),
        ]);

        $response = $this->post('/client/login', [
            'email'    => 'jane@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/client/dashboard');
    }

    public function test_wrong_password_rejected(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        Client::create([
            'first_name' => 'Jane', 'last_name' => 'Smith',
            'email'      => 'jane@example.com',
            'password'   => bcrypt('password123'),
        ]);

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
        $client = Client::create([
            'first_name'     => 'Jane', 'last_name' => 'Smith',
            'email'          => 'jane@example.com',
            'password'       => bcrypt('password123'),
            'calendar_token' => 'valid-test-token-abc123',
        ]);

        $response = $this->get('/my/lessons.ics?token=valid-test-token-abc123');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
    }
}
