<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('skill_level')->nullable(); // beginner, intermediate, advanced
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('waiver_signed')->default(false); // inherits from parent client
            $table->timestamps();

            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
