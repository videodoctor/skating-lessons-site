<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            $table->string('payment_type')->default('venmo')->after('payment_method'); // venmo, cash
            $table->timestamp('cash_paid_at')->nullable()->after('payment_type');
            $table->string('cash_marked_by')->nullable()->after('cash_paid_at'); // admin user name
            $table->string('venmo_username')->nullable()->after('cash_marked_by');
            $table->timestamp('venmo_confirmed_at')->nullable()->after('venmo_username');
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('booking_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn(['student_id', 'payment_type', 'cash_paid_at', 'cash_marked_by', 'venmo_username', 'venmo_confirmed_at']);
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
    }
};
