<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_interests', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->after('id');
            $table->string('source', 50)->default('waitlist')->after('service_id');
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('booking_interests', function (Blueprint $table) {
            $table->dropIndex(['service_id']);
            $table->dropColumn(['service_id', 'source']);
        });
    }
};
