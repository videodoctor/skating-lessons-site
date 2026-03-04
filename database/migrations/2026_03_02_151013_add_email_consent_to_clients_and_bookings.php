<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->timestamp('email_consent_at')->nullable()->after('password');
        });
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('email_consent_at')->nullable()->after('notes');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('email_consent_at');
        });
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('email_consent_at');
        });
    }
};
