<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('subject_id');
            $table->unsignedTinyInteger('grade_id');
            $table->string('title', 150);
            $table->unsignedBigInteger('cover_media_id')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->enum('status', ['draft', 'in_review', 'published', 'archived'])->default('draft');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['subject_id', 'grade_id', 'sort_order'], 'uq_units_order');
            $table->index('status', 'idx_units_status');
            $table->foreign('subject_id', 'fk_units_subject')->references('id')->on('subjects')->onDelete('restrict');
            $table->foreign('grade_id', 'fk_units_grade')->references('id')->on('grades')->onDelete('restrict');
            $table->foreign('cover_media_id', 'fk_units_cover')->references('id')->on('media_files')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
