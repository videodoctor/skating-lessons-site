<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Service;
use App\Models\ServiceWaitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ServiceModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(array $attrs = []): Service
    {
        return Service::create(array_merge([
            'name'             => 'Test Service',
            'slug'             => 'test-service-' . uniqid(),
            'description'      => 'Test',
            'price'            => 55.00,
            'duration_minutes' => 30,
            'is_active'        => true,
            'coming_soon'      => false,
            'show_price'       => true,
            'show_duration'    => true,
            'show_features'    => true,
            'show_description' => true,
        ], $attrs));
    }

    // ── Active / Coming Soon ─────────────────────────────────────────────

    public function test_active_service_is_active(): void
    {
        $svc = $this->makeService(['is_active' => true]);
        $this->assertTrue($svc->is_active);
        $this->assertFalse($svc->coming_soon);
    }

    public function test_coming_soon_service_is_not_active(): void
    {
        $svc = $this->makeService(['is_active' => false, 'coming_soon' => true]);
        $this->assertFalse($svc->is_active);
        $this->assertTrue($svc->coming_soon);
    }

    // ── Discounts ────────────────────────────────────────────────────────

    public function test_no_discount_returns_base_price(): void
    {
        $svc = $this->makeService(['price' => 55.00]);
        $this->assertFalse($svc->hasActiveDiscount());
        $this->assertEquals(55.00, $svc->effectivePrice());
    }

    public function test_active_dollar_discount_reduces_price(): void
    {
        $svc = $this->makeService([
            'price'              => 55.00,
            'discount_amount'    => 10.00,
            'discount_type'      => 'dollar',
            'discount_starts_at' => Carbon::yesterday(),
            'discount_ends_at'   => Carbon::tomorrow(),
        ]);
        $this->assertTrue($svc->hasActiveDiscount());
        $this->assertEquals(45.00, $svc->effectivePrice());
    }

    public function test_active_percent_discount_reduces_price(): void
    {
        $svc = $this->makeService([
            'price'              => 100.00,
            'discount_amount'    => 20,
            'discount_type'      => 'percent',
            'discount_starts_at' => Carbon::yesterday(),
            'discount_ends_at'   => Carbon::tomorrow(),
        ]);
        $this->assertTrue($svc->hasActiveDiscount());
        $this->assertEquals(80.00, $svc->effectivePrice());
    }

    public function test_expired_discount_not_applied(): void
    {
        $svc = $this->makeService([
            'price'              => 55.00,
            'discount_amount'    => 10.00,
            'discount_type'      => 'dollar',
            'discount_starts_at' => Carbon::now()->subDays(10),
            'discount_ends_at'   => Carbon::yesterday(),
        ]);
        $this->assertFalse($svc->hasActiveDiscount());
        $this->assertEquals(55.00, $svc->effectivePrice());
    }

    public function test_future_discount_not_applied(): void
    {
        $svc = $this->makeService([
            'price'              => 55.00,
            'discount_amount'    => 10.00,
            'discount_type'      => 'dollar',
            'discount_starts_at' => Carbon::tomorrow(),
            'discount_ends_at'   => Carbon::now()->addDays(10),
        ]);
        $this->assertFalse($svc->hasActiveDiscount());
        $this->assertEquals(55.00, $svc->effectivePrice());
    }

    public function test_discount_label_shows_dollar_off(): void
    {
        $svc = $this->makeService([
            'price'              => 55.00,
            'discount_amount'    => 10.00,
            'discount_type'      => 'dollar',
            'discount_starts_at' => Carbon::yesterday(),
            'discount_ends_at'   => Carbon::tomorrow(),
        ]);
        $this->assertStringContainsString('10', $svc->discountLabel());
    }

    public function test_discount_label_shows_percent_off(): void
    {
        $svc = $this->makeService([
            'price'              => 100.00,
            'discount_amount'    => 20,
            'discount_type'      => 'percent',
            'discount_starts_at' => Carbon::yesterday(),
            'discount_ends_at'   => Carbon::tomorrow(),
        ]);
        $label = $svc->discountLabel();
        $this->assertStringContainsString('20', $label);
        $this->assertStringContainsString('%', $label);
    }

    // ── Waitlist ─────────────────────────────────────────────────────────

    public function test_waitlist_entry_created(): void
    {
        $svc = $this->makeService(['coming_soon' => true]);

        ServiceWaitlist::create([
            'service_id' => $svc->id,
            'email'      => 'user@example.com',
            'name'       => 'Test User',
        ]);

        $this->assertDatabaseHas('service_waitlist', [
            'service_id' => $svc->id,
            'email'      => 'user@example.com',
        ]);
    }

    public function test_duplicate_waitlist_entry_ignored(): void
    {
        $svc = $this->makeService(['coming_soon' => true]);

        ServiceWaitlist::firstOrCreate(
            ['service_id' => $svc->id, 'email' => 'user@example.com'],
            ['name' => 'Test User']
        );
        ServiceWaitlist::firstOrCreate(
            ['service_id' => $svc->id, 'email' => 'user@example.com'],
            ['name' => 'Test User Again']
        );

        $this->assertEquals(1, ServiceWaitlist::where([
            'service_id' => $svc->id, 'email' => 'user@example.com'
        ])->count());
    }
}
