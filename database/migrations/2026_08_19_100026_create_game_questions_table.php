<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->unsignedBigInteger('question_id');
            $table->smallInteger('sort_order')->default(0);

            $table->unique(['game_id', 'question_id'], 'uq_gq_game_question');
            $table->unique(['game_id', 'sort_order'], 'uq_gq_game_order');
            $table->foreign('game_id', 'fk_gq_game')->references('id')->on('games')->onDelete('cascade');
            $table->foreign('question_id', 'fk_gq_question')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_questions');
    }
};
