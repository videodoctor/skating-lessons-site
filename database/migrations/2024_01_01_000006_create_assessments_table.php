<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('student_name');
            $table->integer('student_age');
            $table->date('assessment_date');
            $table->enum('status', ['scheduled', 'in-progress', 'completed', 'report-sent'])->default('scheduled');
            $table->json('skill_ratings')->nullable();
            $table->text('overall_notes')->nullable();
            $table->json('strengths')->nullable();
            $table->json('improvement_areas')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('report_pdf_path')->nullable();
            $table->timestamps();
            
            $table->index(['assessment_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
