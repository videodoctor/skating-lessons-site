<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Make student_age nullable
            $table->integer('student_age')->nullable()->change();
            
            // Add new client info columns
            $table->string('client_name')->after('client_id');
            $table->string('client_email')->after('client_name');
            $table->string('client_phone')->after('client_email');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('student_age')->nullable(false)->change();
            $table->dropColumn(['client_name', 'client_email', 'client_phone']);
        });
    }
};
