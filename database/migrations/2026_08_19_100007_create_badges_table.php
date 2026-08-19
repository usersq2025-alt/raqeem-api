<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            $table->enum('threshold_type', ['streak_days'])->default('streak_days');
            $table->integer('threshold_value');
            $table->string('icon_url', 255)->nullable();

            $table->unique('code', 'uq_badges_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
