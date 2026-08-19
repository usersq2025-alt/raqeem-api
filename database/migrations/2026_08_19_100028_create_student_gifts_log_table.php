<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_gifts_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('unit_id');
            $table->enum('reward_type', ['points', 'item']);
            $table->unsignedInteger('points_amount')->nullable();
            $table->unsignedBigInteger('store_item_id')->nullable();
            $table->timestamp('granted_at')->useCurrent();

            $table->unique(['student_id', 'unit_id'], 'uq_sgl_student_unit');
            $table->foreign('student_id', 'fk_sgl_student')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('unit_id', 'fk_sgl_unit')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('store_item_id', 'fk_sgl_item')->references('id')->on('store_items')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_gifts_log');
    }
};
