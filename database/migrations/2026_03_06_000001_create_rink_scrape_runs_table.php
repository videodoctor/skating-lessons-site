<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rink_scrape_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rink_id')->constrained()->onDelete('cascade');
            $table->smallInteger('month');
            $table->smallInteger('year');
            $table->string('source_file_path')->nullable();  // path in storage/app/scrapes/
            $table->string('source_type')->default('pdf');   // pdf, image, html
            $table->string('source_url')->nullable();        // original URL scraped from
            $table->integer('sessions_found')->default(0);
            $table->integer('sessions_added')->default(0);
            $table->integer('sessions_removed')->default(0);
            $table->text('scrape_log')->nullable();          // full output log
            $table->boolean('had_errors')->default(false);
            $table->timestamp('scraped_at');
            $table->timestamps();

            $table->index(['rink_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rink_scrape_runs');
    }
};
