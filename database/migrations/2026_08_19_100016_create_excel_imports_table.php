<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excel_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_user_id');
            $table->string('file_name', 255);
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('admin_user_id', 'fk_ei_admin')->references('id')->on('admin_users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excel_imports');
    }
};
