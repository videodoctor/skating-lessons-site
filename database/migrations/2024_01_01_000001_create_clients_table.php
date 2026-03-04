<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('access_token')->unique();
            $table->timestamps();
            
            $table->index('access_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
