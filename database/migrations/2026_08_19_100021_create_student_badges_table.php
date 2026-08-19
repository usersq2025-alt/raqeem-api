<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('badge_id');
            $table->timestamp('earned_at')->useCurrent();

            $table->unique(['student_id', 'badge_id'], 'uq_sb_student_badge');
            $table->foreign('student_id', 'fk_sb_student')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('badge_id', 'fk_sb_badge')->references('id')->on('badges')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_badges');
    }
};
