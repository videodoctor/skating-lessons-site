<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('media_id');
            $table->string('type', 30); // trim, zip_extract
            $table->string('status', 20)->default('pending'); // pending, downloading, processing, uploading, complete, failed
            $table->unsignedTinyInteger('progress')->default(0); // 0-100
            $table->string('message', 500)->nullable();
            $table->timestamps();
            $table->index('media_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_jobs');
    }
};
