<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->unsignedTinyInteger('level');
            $table->string('name_ar', 40);
            $table->string('name_en', 40);
            $table->boolean('is_active')->default(true);

            $table->unique('level', 'uq_grades_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
