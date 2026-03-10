<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planner_scans', function (Blueprint $table) {
            $table->string('page_side', 10)->default('both')->after('image_paths');
        });
    }

    public function down(): void
    {
        Schema::table('planner_scans', function (Blueprint $table) {
            $table->dropColumn('page_side');
        });
    }
};
