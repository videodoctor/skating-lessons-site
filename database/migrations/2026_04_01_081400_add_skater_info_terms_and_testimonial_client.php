<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add skater fields to bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('student_name', 255)->nullable()->after('student_age');
            $table->string('skill_level', 50)->nullable()->after('student_name');
        });

        // 2. Add terms_accepted_at + must_accept_terms to clients
        Schema::table('clients', function (Blueprint $table) {
            $table->timestamp('terms_accepted_at')->nullable()->after('email_consent_at');
            $table->boolean('must_accept_terms')->default(false)->after('terms_accepted_at');
        });

        // 3. Add optional client_id to testimonials
        Schema::table('testimonials', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->after('id');
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['student_name', 'skill_level']);
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['terms_accepted_at', 'must_accept_terms']);
        });
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
