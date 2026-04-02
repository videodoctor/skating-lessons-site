<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_user_id')->nullable()->after('client_id');
            $table->index('admin_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            $table->dropIndex(['admin_user_id']);
            $table->dropColumn('admin_user_id');
        });
    }
};
