<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('to_number');               // E.164 phone number
            $table->text('message_body');              // full SMS text sent
            $table->string('twilio_sid')->nullable();  // Twilio message SID
            $table->string('status')->default('pending'); // pending, sent, delivered, failed
            $table->string('reply')->nullable();       // YES, NO, or raw reply text
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('scheduled_for');        // when it should be sent (30hr before)
            $table->timestamps();

            $table->index('booking_id');
            $table->index('status');
            $table->index('scheduled_for');
        });

        // Add SMS opt-in column to clients
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('sms_consent')->default(false)->after('email_consent_at');
            $table->string('sms_phone')->nullable()->after('sms_consent'); // normalized E.164
        });

        // Add reminder_sent / confirmed_via_sms to bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('reminder_sent')->default(false)->after('notes');
            $table->boolean('confirmed_via_sms')->default(false)->after('reminder_sent');
            $table->timestamp('sms_confirmed_at')->nullable()->after('confirmed_via_sms');
            $table->timestamp('sms_cancelled_at')->nullable()->after('sms_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_reminders');
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['sms_consent', 'sms_phone']);
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['reminder_sent', 'confirmed_via_sms', 'sms_confirmed_at', 'sms_cancelled_at']);
        });
    }
};
