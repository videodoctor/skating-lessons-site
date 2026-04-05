<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_media_id')->constrained('student_media')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('path');
            $table->string('edit_type', 50); // original, crop, trim, adjust
            $table->json('edit_params')->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->decimal('duration', 8, 2)->nullable();
            $table->string('created_by_type', 20)->default('admin');
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->timestamps();

            $table->index(['student_media_id', 'version']);
        });

        // Backfill existing media with version records
        $media = DB::table('student_media')->get();
        foreach ($media as $m) {
            $version = 1;

            // If there's an original_path, that's version 1
            if ($m->original_path) {
                DB::table('media_versions')->insert([
                    'student_media_id' => $m->id,
                    'version'          => $version,
                    'path'             => $m->original_path,
                    'edit_type'        => 'original',
                    'edit_params'      => null,
                    'file_size'        => null,
                    'width'            => null,
                    'height'           => null,
                    'duration'         => null,
                    'created_by_type'  => $m->uploaded_by_type ?? 'admin',
                    'created_by_id'    => $m->uploaded_by_id,
                    'created_at'       => $m->created_at,
                    'updated_at'       => $m->created_at,
                ]);
                $version++;

                // Current path is version 2 (the edit)
                DB::table('media_versions')->insert([
                    'student_media_id' => $m->id,
                    'version'          => $version,
                    'path'             => $m->path,
                    'edit_type'        => $m->type === 'video' ? 'trim' : 'crop',
                    'edit_params'      => null,
                    'file_size'        => $m->file_size,
                    'width'            => $m->width,
                    'height'           => $m->height,
                    'duration'         => $m->duration,
                    'created_by_type'  => $m->uploaded_by_type ?? 'admin',
                    'created_by_id'    => $m->uploaded_by_id,
                    'created_at'       => $m->updated_at,
                    'updated_at'       => $m->updated_at,
                ]);
            } else {
                // No edits — just the original
                DB::table('media_versions')->insert([
                    'student_media_id' => $m->id,
                    'version'          => 1,
                    'path'             => $m->path,
                    'edit_type'        => 'original',
                    'edit_params'      => null,
                    'file_size'        => $m->file_size,
                    'width'            => $m->width,
                    'height'           => $m->height,
                    'duration'         => $m->duration,
                    'created_by_type'  => $m->uploaded_by_type ?? 'admin',
                    'created_by_id'    => $m->uploaded_by_id,
                    'created_at'       => $m->created_at,
                    'updated_at'       => $m->created_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('media_versions');
    }
};
