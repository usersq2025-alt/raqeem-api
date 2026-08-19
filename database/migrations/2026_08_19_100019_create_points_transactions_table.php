<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->enum('type', ['lesson_complete', 'review_station', 'gift', 'purchase', 'initial_grant', 'adjustment']);
            $table->integer('points_change')->comment('موجب = كسب, سالب = إنفاق');
            $table->string('reference_type', 60)->nullable()->comment('مثال: student_lesson_attempts');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['student_id', 'created_at'], 'idx_pt_student_created');
            $table->foreign('student_id', 'fk_pt_student')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_transactions');
    }
};
