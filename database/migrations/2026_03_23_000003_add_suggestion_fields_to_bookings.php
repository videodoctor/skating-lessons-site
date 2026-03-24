<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('suggested_time_slot_id')->nullable()->after('time_slot_id');
            $table->text('suggestion_message')->nullable()->after('suggested_time_slot_id');
            $table->string('suggestion_token', 64)->nullable()->unique()->after('suggestion_message');
            $table->timestamp('suggestion_sent_at')->nullable()->after('suggestion_token');
            $table->timestamp('suggestion_responded_at')->nullable()->after('suggestion_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'suggested_time_slot_id',
                'suggestion_message',
                'suggestion_token',
                'suggestion_sent_at',
                'suggestion_responded_at',
            ]);
        });
    }
};
