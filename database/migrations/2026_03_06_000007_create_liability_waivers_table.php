<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liability_waivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('waiver_version'); // e.g. "1.0", "1.1"
            $table->text('waiver_text');      // full text of waiver at time of signing
            $table->string('signed_name');    // typed full name as signature
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('student_ids');      // which students this covers
            $table->timestamp('signed_at');
            $table->timestamps();

            $table->index('client_id');
            $table->index('waiver_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liability_waivers');
    }
};
