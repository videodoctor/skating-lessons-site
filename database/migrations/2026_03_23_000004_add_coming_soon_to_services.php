<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('coming_soon')->default(false)->after('is_active');
            $table->string('coming_soon_teaser')->nullable()->after('coming_soon');
            $table->boolean('show_price')->default(true)->after('coming_soon_teaser');
            $table->boolean('show_duration')->default(true)->after('show_price');
            $table->boolean('show_features')->default(true)->after('show_duration');
            $table->boolean('show_description')->default(true)->after('show_features');
        });

        Schema::create('service_waitlist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->string('email');
            $table->string('name')->nullable();
            $table->timestamps();
            $table->unique(['service_id', 'email']);
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_waitlist');
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['coming_soon', 'coming_soon_teaser', 'show_price', 'show_duration', 'show_features', 'show_description']);
        });
    }
};
