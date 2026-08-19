<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lesson_id');
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->unsignedTinyInteger('game_type_id')->comment('يحدد بنية payload');
            $table->text('question_text');
            $table->unsignedBigInteger('image_media_id')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->json('payload')->comment('خيارات/إجابة صحيحة/بنية خاصة حسب النمط');
            $table->text('explanation')->nullable()->comment('شرح الإجابة - غير مُفعَّل بواجهة الطالب حالياً');
            $table->enum('status', ['draft', 'in_review', 'published', 'archived'])->default('draft');
            $table->enum('source', ['manual', 'import'])->default('manual');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('lesson_id', 'idx_questions_lesson');
            $table->index('skill_id', 'idx_questions_skill');
            $table->index('status', 'idx_questions_status');
            $table->foreign('lesson_id', 'fk_questions_lesson')->references('id')->on('lessons')->onDelete('cascade');
            $table->foreign('skill_id', 'fk_questions_skill')->references('id')->on('skills')->onDelete('set null');
            $table->foreign('game_type_id', 'fk_questions_game_type')->references('id')->on('game_types')->onDelete('restrict');
            $table->foreign('image_media_id', 'fk_questions_image')->references('id')->on('media_files')->onDelete('set null');
            $table->foreign('created_by', 'fk_questions_created_by')->references('id')->on('admin_users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
