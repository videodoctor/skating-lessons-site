<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add phone verification to clients
        Schema::table('clients', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('email_verify_token', 64)->nullable()->after('phone_verified_at');
            $table->string('phone_verify_code', 6)->nullable()->after('email_verify_token');
            $table->timestamp('phone_verify_sent_at')->nullable()->after('phone_verify_code');
        });

        // Add guest conversion token to bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('guest_convert_token', 64)->nullable()->after('confirmation_code');
            $table->boolean('guest_sms_consent')->default(false)->after('guest_convert_token');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['phone_verified_at', 'email_verify_token', 'phone_verify_code', 'phone_verify_sent_at']);
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['guest_convert_token', 'guest_sms_consent']);
        });
    }
};
