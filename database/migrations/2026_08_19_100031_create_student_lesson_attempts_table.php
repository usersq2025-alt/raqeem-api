<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_lesson_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('lesson_id');
            $table->smallInteger('attempt_number')->default(1);
            $table->enum('status', ['in_progress', 'battery_depleted', 'completed'])->default('in_progress');
            $table->integer('correct_count')->default(0);
            $table->integer('wrong_count')->default(0);
            $table->tinyInteger('stars')->nullable()->comment('1-3, يُحسب عند الإتمام');
            $table->integer('points_earned')->default(0);
            $table->unsignedBigInteger('current_game_id')->nullable()->comment('لاستئناف الدرس');
            $table->unsignedBigInteger('current_question_id')->nullable();
            $table->timestamp('recharge_ends_at')->nullable()->comment('وقت انتهاء شاشة B6-Recharge (توقيت خادم)');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->index(['student_id', 'lesson_id'], 'idx_sla_student_lesson');
            $table->index('status', 'idx_sla_status');
            $table->foreign('lesson_id', 'fk_sla_lesson')->references('id')->on('lessons')->onDelete('cascade');
            $table->foreign('student_id', 'fk_sla_student')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('current_game_id', 'fk_sla_game')->references('id')->on('games')->onDelete('set null');
            $table->foreign('current_question_id', 'fk_sla_question')->references('id')->on('questions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_lesson_attempts');
    }
};
