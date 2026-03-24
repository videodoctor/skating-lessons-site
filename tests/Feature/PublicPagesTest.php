<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Rink;
use App\Models\Service;
use App\Models\ServiceWaitlist;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PublicPagesTest extends TestCase
{
    use DatabaseTransactions;

    private function makeRink(array $attrs = []): Rink
    {
        return Rink::create(array_merge([
            'name'         => 'Test Rink',
            'slug'         => 'test-rink-' . uniqid(),
            'is_active'    => true,
            'schedule_url' => 'https://example.com/schedule',
        ], $attrs));
    }

    // ── Core pages ───────────────────────────────────────────────────────

    public function test_home_page_loads(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_rinks_page_loads(): void
    {
        $this->get('/rinks')->assertStatus(200);
    }

    public function test_sms_opt_in_page_loads_without_login(): void
    {
        $response = $this->get('/sms-opt-in');
        $response->assertStatus(200);
    }

    public function test_terms_page_loads(): void
    {
        $this->get('/terms-and-conditions')->assertStatus(200);
    }

    public function test_privacy_page_loads(): void
    {
        $this->get('/privacy-policy')->assertStatus(200);
    }

    // ── Rinks page ───────────────────────────────────────────────────────

    public function test_inactive_rinks_not_shown(): void
    {
        $this->makeRink(['name' => 'Active Rink',   'slug' => 'active-rink',   'is_active' => true]);
        $this->makeRink(['name' => 'Inactive Rink', 'slug' => 'inactive-rink', 'is_active' => false]);

        $response = $this->get('/rinks');
        $response->assertSee('Active Rink');
        $response->assertDontSee('Inactive Rink');
    }

    // ── Waitlist ─────────────────────────────────────────────────────────

    public function test_waitlist_join_works(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $service = Service::create([
            'name' => 'Assessment', 'slug' => 'assessment',
            'description' => 'Full assessment', 'price' => 120,
            'duration_minutes' => 60, 'is_active' => false, 'coming_soon' => true,
        ]);

        $this->post("/waitlist/{$service->id}", [
            'email' => 'interested@example.com',
            'name'  => 'Interested User',
        ]);

        $this->assertDatabaseHas('service_waitlist', [
            'service_id' => $service->id,
            'email'      => 'interested@example.com',
        ]);
    }

    public function test_waitlist_join_requires_valid_email(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $service = Service::create([
            'name' => 'Assessment', 'slug' => 'assessment2',
            'description' => 'Full assessment', 'price' => 120,
            'duration_minutes' => 60, 'is_active' => false, 'coming_soon' => true,
        ]);

        $response = $this->post("/waitlist/{$service->id}", ['email' => 'not-an-email']);
        $response->assertSessionHasErrors('email');
    }

    public function test_duplicate_waitlist_join_is_idempotent(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $service = Service::create([
            'name' => 'Assessment', 'slug' => 'assessment3',
            'description' => 'Full assessment', 'price' => 120,
            'duration_minutes' => 60, 'is_active' => false, 'coming_soon' => true,
        ]);

        $this->post("/waitlist/{$service->id}", ['email' => 'user@example.com']);
        $this->post("/waitlist/{$service->id}", ['email' => 'user@example.com']);

        $this->assertEquals(1, ServiceWaitlist::where([
            'service_id' => $service->id,
            'email'      => 'user@example.com',
        ])->count());
    }

    // ── Admin redirect ───────────────────────────────────────────────────

    public function test_admin_dashboard_redirects_without_auth(): void
    {
        $response = $this->get('/admin/dashboard');
        // Admin routes use standard Laravel auth — redirects somewhere with 'login'
        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('login', $location);
    }

    public function test_booking_page_loads(): void
    {
        $this->get('/book')->assertStatus(200);
    }
}
