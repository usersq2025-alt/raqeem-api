<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_types', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('code', 40);
            $table->string('name_ar', 80);
            $table->string('name_en', 80);
            $table->boolean('is_active')->default(true);

            $table->unique('code', 'uq_game_types_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_types');
    }
};
