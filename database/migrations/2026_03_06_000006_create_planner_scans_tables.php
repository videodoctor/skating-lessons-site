<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each upload session (1-2 photos = one scan)
        Schema::create('planner_scans', function (Blueprint $table) {
            $table->id();
            $table->string('month');
            $table->smallInteger('year');
            $table->json('image_paths'); // stored paths of uploaded photos
            $table->integer('entries_extracted')->default(0);
            $table->integer('entries_confirmed')->default(0);
            $table->boolean('is_finalized')->default(false);
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Individual entries extracted from a scan
        Schema::create('planner_scan_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planner_scan_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('raw_text')->nullable();       // what Claude read verbatim
            $table->string('type');                       // private_lesson, lts, ltp, cancelled_public, personal_block, note
            $table->string('rink')->nullable();
            $table->string('extracted_name')->nullable(); // raw name from planner
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('confidence')->default(100); // 0-100
            $table->string('match_status')->nullable();  // matched, unmatched, no_booking_expected, personal
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['planner_scan_id', 'date']);
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planner_scan_entries');
        Schema::dropIfExists('planner_scans');
    }
};
