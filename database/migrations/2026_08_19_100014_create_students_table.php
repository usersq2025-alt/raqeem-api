<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 12)->comment('RQMS-XXXXXX, not shown to parent');
            $table->unsignedBigInteger('parent_id');
            $table->string('full_name', 120);
            $table->date('birth_date');
            $table->unsignedTinyInteger('grade_id');
            $table->enum('gender', ['male', 'female']);
            $table->unsignedTinyInteger('profession_id')->nullable();
            $table->integer('points_balance')->default(0)->comment('cache, source of truth = points_transactions');
            $table->integer('streak_current')->default(0)->comment('cache, source of truth = daily_activity_log');
            $table->integer('streak_longest')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->enum('status', ['active', 'frozen', 'deleted'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique('public_id', 'uq_students_public_id');
            $table->index('parent_id', 'idx_students_parent');
            $table->index('grade_id', 'idx_students_grade');
            $table->foreign('parent_id', 'fk_students_parent')->references('id')->on('parents')->onDelete('cascade');
            $table->foreign('grade_id', 'fk_students_grade')->references('id')->on('grades')->onDelete('restrict');
            $table->foreign('profession_id', 'fk_students_profession')->references('id')->on('professions')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
