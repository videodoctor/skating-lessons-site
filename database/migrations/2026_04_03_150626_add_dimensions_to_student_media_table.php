<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_media', function (Blueprint $table) {
            $table->unsignedSmallInteger('width')->nullable()->after('file_size');
            $table->unsignedSmallInteger('height')->nullable()->after('width');
            $table->decimal('duration', 8, 2)->nullable()->after('height'); // video duration in seconds
        });
    }

    public function down(): void
    {
        Schema::table('student_media', function (Blueprint $table) {
            $table->dropColumn(['width', 'height', 'duration']);
        });
    }
};
