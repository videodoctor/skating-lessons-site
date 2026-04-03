<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('referred_by', 255)->nullable()->after('notes');
        });
        Schema::table('booking_interests', function (Blueprint $table) {
            $table->string('referred_by', 255)->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('referred_by');
        });
        Schema::table('booking_interests', function (Blueprint $table) {
            $table->dropColumn('referred_by');
        });
    }
};
