<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rinks', function (Blueprint $table) {
            $table->boolean('is_bookable')->default(true)->after('is_active');
        });

        // Default: match current is_active state
        DB::table('rinks')->update(['is_bookable' => DB::raw('is_active')]);
    }

    public function down(): void
    {
        Schema::table('rinks', function (Blueprint $table) {
            $table->dropColumn('is_bookable');
        });
    }
};
