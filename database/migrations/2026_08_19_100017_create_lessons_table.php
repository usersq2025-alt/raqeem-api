<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');
            $table->string('title', 150);
            $table->longText('body_content')->nullable()->comment('شرح/محتوى نصي وصوري للدرس');
            $table->smallInteger('sort_order')->default(0);
            $table->enum('status', ['draft', 'in_review', 'published', 'archived'])->default('draft');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['unit_id', 'sort_order'], 'uq_lessons_order');
            $table->index('status', 'idx_lessons_status');
            $table->foreign('unit_id', 'fk_lessons_unit')->references('id')->on('units')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
