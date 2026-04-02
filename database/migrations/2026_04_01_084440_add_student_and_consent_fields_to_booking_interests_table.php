<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_interests', function (Blueprint $table) {
            $table->string('student_name', 255)->nullable()->after('message');
            $table->unsignedTinyInteger('student_age')->nullable()->after('student_name');
            $table->string('skill_level', 50)->nullable()->after('student_age');
            $table->boolean('email_consent')->default(false)->after('skill_level');
            $table->boolean('sms_consent')->default(false)->after('email_consent');
        });
    }

    public function down(): void
    {
        Schema::table('booking_interests', function (Blueprint $table) {
            $table->dropColumn(['student_name', 'student_age', 'skill_level', 'email_consent', 'sms_consent']);
        });
    }
};
