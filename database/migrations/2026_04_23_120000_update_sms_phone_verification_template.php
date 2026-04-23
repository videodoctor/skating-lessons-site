<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('notification_templates')
            ->where('key', 'sms_phone_verification')
            ->update([
                'body' => 'Your Kristine Skates verification code is: {{code}}. This code expires in 10 minutes. Reply STOP to opt out. — Kristine Skates',
                'updated_at' => now(),
            ]);

        Cache::forget('notif_template:sms_phone_verification');
    }

    public function down(): void
    {
        DB::table('notification_templates')
            ->where('key', 'sms_phone_verification')
            ->update([
                'body' => 'Your Kristine Skates verification code is: {{code}}. This code expires in 10 minutes.',
                'updated_at' => now(),
            ]);

        Cache::forget('notif_template:sms_phone_verification');
    }
};
