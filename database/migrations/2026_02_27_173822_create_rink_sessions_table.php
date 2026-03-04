<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rink_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rink_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('session_type')->default('public_skate');
            $table->text('notes')->nullable();
            $table->boolean('is_cancelled')->default(false);
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();
            
            $table->index(['rink_id', 'date']);
            $table->unique(['rink_id', 'date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rink_sessions');
    }
};
