<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_interests', function (Blueprint $table) {
            $table->boolean('waiver_accepted')->default(false)->after('sms_consent');
            $table->boolean('terms_accepted')->default(false)->after('waiver_accepted');
        });
    }

    public function down(): void
    {
        Schema::table('booking_interests', function (Blueprint $table) {
            $table->dropColumn(['waiver_accepted', 'terms_accepted']);
        });
    }
};
