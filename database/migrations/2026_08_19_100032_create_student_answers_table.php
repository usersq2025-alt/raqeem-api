<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id');
            $table->unsignedBigInteger('game_id');
            $table->unsignedBigInteger('question_id');
            $table->boolean('is_correct');
            $table->json('selected_answer')->nullable();
            $table->timestamp('answered_at')->useCurrent();

            $table->index('attempt_id', 'idx_sa_attempt');
            $table->index('question_id', 'idx_sa_question');
            $table->index('is_correct', 'idx_sa_is_correct');
            $table->foreign('attempt_id', 'fk_sa_attempt')->references('id')->on('student_lesson_attempts')->onDelete('cascade');
            $table->foreign('game_id', 'fk_sa_game')->references('id')->on('games')->onDelete('cascade');
            $table->foreign('question_id', 'fk_sa_question')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
