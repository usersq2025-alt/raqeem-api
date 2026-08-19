<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_station_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('unit_id');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->integer('points_earned')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['student_id', 'unit_id'], 'uq_rss_student_unit');
            $table->foreign('student_id', 'fk_rss_student')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('unit_id', 'fk_rss_unit')->references('id')->on('units')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_station_sessions');
    }
};
