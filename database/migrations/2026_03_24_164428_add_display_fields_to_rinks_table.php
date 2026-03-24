<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rinks', function (Blueprint $table) {
            $table->boolean('is_displayed')->default(true)->after('is_active');
            $table->string('inactive_message')->nullable()->after('is_displayed');
        });

        // Set existing data: displayed = active by default, except Kirkwood
        DB::table('rinks')->update(['is_displayed' => DB::raw('is_active')]);
        DB::table('rinks')->where('slug', 'kirkwood')->update([
            'is_displayed' => true,
            'inactive_message' => 'Closed for updates until July 31st',
        ]);
    }

    public function down(): void
    {
        Schema::table('rinks', function (Blueprint $table) {
            $table->dropColumn(['is_displayed', 'inactive_message']);
        });
    }
};
