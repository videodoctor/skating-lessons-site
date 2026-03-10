<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planner_scan_entries', function (Blueprint $table) {
            $table->json('bbox')->nullable()->after('notes');
            $table->tinyInteger('image_index')->default(0)->after('bbox');
        });
    }

    public function down(): void
    {
        Schema::table('planner_scan_entries', function (Blueprint $table) {
            $table->dropColumn(['bbox', 'image_index']);
        });
    }
};
