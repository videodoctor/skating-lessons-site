<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->foreignId('rink_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('rink_session_id')->nullable()->after('rink_id')->constrained()->nullOnDelete();
            $table->integer('priority')->default(0)->after('is_available');
            $table->boolean('is_pre_allocated')->default(false)->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropForeign(['rink_id']);
            $table->dropForeign(['rink_session_id']);
            $table->dropColumn(['rink_id', 'rink_session_id', 'priority', 'is_pre_allocated']);
        });
    }
};
