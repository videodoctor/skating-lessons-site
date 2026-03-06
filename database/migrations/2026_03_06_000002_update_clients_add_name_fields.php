<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->text('notes')->nullable()->after('phone');
            $table->timestamp('waiver_signed_at')->nullable()->after('notes');
            $table->string('waiver_version')->nullable()->after('waiver_signed_at');
            $table->string('waiver_ip')->nullable()->after('waiver_version');
        });

        // Backfill first_name/last_name from existing name field
        DB::statement("UPDATE clients SET first_name = SUBSTRING_INDEX(name, ' ', 1), last_name = SUBSTRING(name, LOCATE(' ', name) + 1) WHERE name IS NOT NULL AND name != ''");
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'notes', 'waiver_signed_at', 'waiver_version', 'waiver_ip']);
        });
    }
};
