<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_media', function (Blueprint $table) {
            $table->string('original_path')->nullable()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('student_media', function (Blueprint $table) {
            $table->dropColumn('original_path');
        });
    }
};
