<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excel_import_rows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('import_id');
            $table->unsignedInteger('row_number');
            $table->json('raw_data');
            $table->json('mapped_data')->nullable();
            $table->enum('status', ['pending', 'valid', 'error', 'imported', 'ignored'])->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('created_question_id')->nullable();

            $table->index(['import_id', 'status'], 'idx_eir_import_status');
            $table->foreign('import_id', 'fk_eir_import')->references('id')->on('excel_imports')->onDelete('cascade');
            $table->foreign('created_question_id', 'fk_eir_question')->references('id')->on('questions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excel_import_rows');
    }
};
