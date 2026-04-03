<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['photo', 'video']);
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->string('caption', 500)->nullable();
            $table->boolean('is_profile_photo')->default(false);
            $table->string('uploaded_by_type', 20)->default('admin'); // admin or client
            $table->unsignedBigInteger('uploaded_by_id')->nullable();
            $table->timestamps();

            $table->index('student_id');
            $table->index('type');
        });

        // Add profile_photo_id to students
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('profile_photo_id')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('profile_photo_id');
        });
        Schema::dropIfExists('student_media');
    }
};
