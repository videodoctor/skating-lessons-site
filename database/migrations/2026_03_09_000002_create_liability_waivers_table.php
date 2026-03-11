<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create liability_waivers table if not exists
        if (!Schema::hasTable('liability_waivers')) {
            Schema::create('liability_waivers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->onDelete('cascade');
                $table->string('version', 20)->default('1.0');
                $table->string('signed_name');
                $table->string('signed_ip', 45)->nullable();
                $table->timestamp('signed_at');
                $table->text('waiver_text_snapshot')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('liability_waivers');
    }
};
