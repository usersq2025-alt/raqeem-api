<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique('name', 'uq_skills_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};
