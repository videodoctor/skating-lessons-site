<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Rink;
use App\Models\Service;
use App\Models\ServiceWaitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

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
        // Critical for A2P compliance — must be publicly accessible
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

    // ── Rinks page filters ───────────────────────────────────────────────

    public function test_inactive_rinks_not_shown(): void
    {
        Rink::create(['name' => 'Active Rink',   'slug' => 'active-rink',   'is_active' => true]);
        Rink::create(['name' => 'Inactive Rink', 'slug' => 'inactive-rink', 'is_active' => false]);

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

        $response = $this->post("/waitlist/{$service->id}", [
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

        $response = $this->post("/waitlist/{$service->id}", [
            'email' => 'not-an-email',
        ]);

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

    public function test_admin_dashboard_redirects_without_session(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_login_page_loads(): void
    {
        $this->get('/admin/login')->assertStatus(200);
    }
}
