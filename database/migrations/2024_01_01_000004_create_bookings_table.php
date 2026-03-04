<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained();
            $table->unsignedBigInteger('time_slot_id');
            $table->integer('student_age');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['pending', 'confirmed', 'paid', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['venmo', 'cash', 'check', 'other'])->default('venmo');
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->string('venmo_transaction_id')->nullable();
            $table->decimal('price_paid', 8, 2);
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('confirmation_code', 8)->unique();
            $table->timestamps();
            
            $table->index(['date', 'status']);
            $table->index('payment_status');
            $table->index('confirmation_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
