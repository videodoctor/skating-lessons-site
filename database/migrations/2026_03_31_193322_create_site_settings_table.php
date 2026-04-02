<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });

        DB::table('site_settings')->insert([
            ['key' => 'booking_paused', 'value' => '0'],
            ['key' => 'booking_paused_message', 'value' => 'Booking is currently closed for the season. Join our waitlist to be notified when lessons resume!'],
            ['key' => 'booking_opens_at', 'value' => null],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
