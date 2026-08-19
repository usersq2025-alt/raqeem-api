<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professions', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('code', 30);
            $table->string('name_ar', 60);
            $table->string('name_en', 60);
            $table->string('avatar_asset_url', 255);
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->unique('code', 'uq_professions_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professions');
    }
};
