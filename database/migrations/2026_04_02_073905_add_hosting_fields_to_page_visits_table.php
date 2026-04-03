<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            $table->string('org', 255)->nullable()->after('city');
            $table->string('isp', 255)->nullable()->after('org');
            $table->boolean('is_hosting')->default(false)->after('isp');
            $table->index('is_hosting');
        });
    }

    public function down(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            $table->dropIndex(['is_hosting']);
            $table->dropColumn(['org', 'isp', 'is_hosting']);
        });
    }
};
