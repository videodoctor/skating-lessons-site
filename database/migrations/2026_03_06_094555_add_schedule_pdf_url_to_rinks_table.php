<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('rinks', function (Blueprint $table) {
            $table->string('schedule_pdf_url')->nullable()->after('schedule_url');
        });
    }
    public function down(): void {
        Schema::table('rinks', function (Blueprint $table) {
            $table->dropColumn('schedule_pdf_url');
        });
    }
};
