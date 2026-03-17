<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('discount_amount', 8, 2)->nullable()->after('price');
            $table->enum('discount_type', ['percent', 'dollar'])->nullable()->after('discount_amount');
            $table->date('discount_starts_at')->nullable()->after('discount_type');
            $table->date('discount_ends_at')->nullable()->after('discount_starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_type', 'discount_starts_at', 'discount_ends_at']);
        });
    }
};
