<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lesson_id');
            $table->unsignedTinyInteger('game_type_id');
            $table->string('title', 150)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('randomize_order')->default(false);
            $table->json('config')->nullable()->comment('حجم الشبكة، مدة المؤقت..الخ');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['lesson_id', 'sort_order'], 'uq_games_order');
            $table->index('lesson_id', 'idx_games_lesson');
            $table->foreign('lesson_id', 'fk_games_lesson')->references('id')->on('lessons')->onDelete('cascade');
            $table->foreign('game_type_id', 'fk_games_type')->references('id')->on('game_types')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
