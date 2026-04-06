<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('label', 255);
            $table->string('category', 50);
            $table->string('channel', 10); // sms or email
            $table->string('subject', 500)->nullable(); // email only
            $table->text('body');
            $table->json('variables')->nullable(); // available placeholder vars
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default templates
        $now = now();
        DB::table('notification_templates')->insert([
            // SMS templates
            [
                'key' => 'sms_opt_in_confirmation',
                'label' => 'SMS Opt-In Confirmation',
                'category' => 'account_security',
                'channel' => 'sms',
                'subject' => null,
                'body' => 'You are now opted in to SMS notifications from Kristine Skates. Msg frequency varies. Msg & data rates may apply. Reply STOP to cancel or HELP for help. — Kristine Skates',
                'variables' => json_encode(['client_name', 'first_name']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'sms_lesson_reminder',
                'label' => 'Lesson Reminder (30hr)',
                'category' => 'lesson_reminders',
                'channel' => 'sms',
                'subject' => null,
                'body' => 'Reminder: Your skating lesson for {{student_name}} is tomorrow at {{lesson_time}} at {{rink_name}}. ${{price}} due at lesson. Reply YES to confirm or NO to cancel. Cancellations less than 24 hours before the lesson will be billed at the full rate. Reply STOP to opt out or HELP for assistance. — Kristine Skates',
                'variables' => json_encode(['student_name', 'lesson_time', 'lesson_date', 'rink_name', 'price', 'service_name', 'client_name', 'first_name', 'confirmation_code']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'sms_booking_confirmed',
                'label' => 'Booking Confirmed',
                'category' => 'booking_updates',
                'channel' => 'sms',
                'subject' => null,
                'body' => 'Confirmed! Your skating lesson for {{student_name}} is {{lesson_date}} at {{lesson_time}} at {{rink_name}}. Pay ${{price}} at https://kristineskates.com/pay/{{confirmation_code}}. Reply STOP to opt out or HELP for assistance. — Kristine Skates',
                'variables' => json_encode(['student_name', 'lesson_time', 'lesson_date', 'rink_name', 'price', 'service_name', 'client_name', 'first_name', 'confirmation_code']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'sms_booking_rejected',
                'label' => 'Booking Rejected',
                'category' => 'booking_updates',
                'channel' => 'sms',
                'subject' => null,
                'body' => 'Hi {{first_name}}, unfortunately we\'re unable to accommodate your lesson request for {{lesson_date}} at {{lesson_time}}. Please visit https://kristineskates.com/book to request a different time. Reply STOP to opt out or HELP for assistance. — Kristine Skates',
                'variables' => json_encode(['student_name', 'lesson_time', 'lesson_date', 'rink_name', 'service_name', 'client_name', 'first_name']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'sms_payment_reminder',
                'label' => 'Payment Reminder',
                'category' => 'payment_reminders',
                'channel' => 'sms',
                'subject' => null,
                'body' => 'Hi {{first_name}}, a friendly reminder that ${{price}} is due for {{student_name}}\'s lesson on {{lesson_date}}. Pay at https://kristineskates.com/pay/{{confirmation_code}}. Reply STOP to opt out or HELP for assistance. — Kristine Skates',
                'variables' => json_encode(['student_name', 'lesson_date', 'price', 'client_name', 'first_name', 'confirmation_code']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'sms_schedule_change',
                'label' => 'Schedule Change',
                'category' => 'schedule_changes',
                'channel' => 'sms',
                'subject' => null,
                'body' => 'Hi {{first_name}}, your lesson for {{student_name}} on {{lesson_date}} at {{lesson_time}} has been updated. Please check your dashboard at https://kristineskates.com/client/dashboard for details. Reply STOP to opt out or HELP for assistance. — Kristine Skates',
                'variables' => json_encode(['student_name', 'lesson_time', 'lesson_date', 'rink_name', 'client_name', 'first_name']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'sms_availability',
                'label' => 'Availability Notification',
                'category' => 'availability',
                'channel' => 'sms',
                'subject' => null,
                'body' => 'Hi {{first_name}}, lesson times are now available at Kristine Skates! Book at https://kristineskates.com/book. Reply STOP to opt out or HELP for assistance. — Kristine Skates',
                'variables' => json_encode(['client_name', 'first_name']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'sms_public_skate',
                'label' => 'Public Skate Times (SKATE reply)',
                'category' => 'public_skate_times',
                'channel' => 'sms',
                'subject' => null,
                'body' => 'Today\'s public skate times: {{skate_times}}. Reply STOP to opt out or HELP for assistance. — Kristine Skates',
                'variables' => json_encode(['skate_times']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'sms_upcoming_lessons',
                'label' => 'Upcoming Lessons (LESSONS reply)',
                'category' => 'lesson_reminders',
                'channel' => 'sms',
                'subject' => null,
                'body' => 'Upcoming lessons for {{first_name}}: {{lesson_list}}. Reply HELP for assistance or STOP to opt out. — Kristine Skates',
                'variables' => json_encode(['first_name', 'lesson_list']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'sms_phone_verification',
                'label' => 'Phone Verification Code',
                'category' => 'account_security',
                'channel' => 'sms',
                'subject' => null,
                'body' => 'Your Kristine Skates verification code is: {{code}}. This code expires in 10 minutes.',
                'variables' => json_encode(['code']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            // Email templates
            [
                'key' => 'email_booking_requested',
                'label' => 'Booking Request Received',
                'category' => 'booking_updates',
                'channel' => 'email',
                'subject' => 'Lesson Request Received - Kristine Skates',
                'body' => 'Thank you for your lesson request, {{client_name}}! Coach Kristine will review your request and email you confirmation shortly.',
                'variables' => json_encode(['client_name', 'first_name', 'student_name', 'service_name', 'lesson_date', 'lesson_time', 'rink_name', 'confirmation_code']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'email_booking_approved',
                'label' => 'Booking Approved',
                'category' => 'booking_updates',
                'channel' => 'email',
                'subject' => 'Lesson Approved! - {{service_name}}',
                'body' => 'Great news, {{client_name}}! Coach Kristine has approved your skating lesson request.',
                'variables' => json_encode(['client_name', 'first_name', 'student_name', 'service_name', 'lesson_date', 'lesson_time', 'rink_name', 'rink_address', 'price', 'confirmation_code', 'venmo_link']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'email_booking_rejected',
                'label' => 'Booking Rejected',
                'category' => 'booking_updates',
                'channel' => 'email',
                'subject' => 'Lesson Request Update - Kristine Skates',
                'body' => 'Hi {{client_name}}, unfortunately we\'re unable to accommodate your lesson request at this time.',
                'variables' => json_encode(['client_name', 'first_name', 'service_name', 'lesson_date', 'lesson_time']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'email_payment_reminder',
                'label' => 'Payment Reminder',
                'category' => 'payment_reminders',
                'channel' => 'email',
                'subject' => 'Payment Reminder - Kristine Skates',
                'body' => 'Hi {{first_name}}, a friendly reminder that ${{price}} is due for {{student_name}}\'s lesson on {{lesson_date}}.',
                'variables' => json_encode(['client_name', 'first_name', 'student_name', 'lesson_date', 'price', 'confirmation_code', 'venmo_link']),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
