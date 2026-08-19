<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_station_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('question_id');
            $table->boolean('is_correct')->nullable();
            $table->timestamp('answered_at')->nullable();

            $table->unique(['session_id', 'question_id'], 'uq_rsq_session_question');
            $table->foreign('session_id', 'fk_rsq_session')->references('id')->on('review_station_sessions')->onDelete('cascade');
            $table->foreign('question_id', 'fk_rsq_question')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_station_questions');
    }
};
