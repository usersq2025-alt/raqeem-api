<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 120);
            $table->string('email', 190);
            $table->string('password_hash', 255);
            $table->unsignedTinyInteger('role_id');
            $table->enum('status', ['active', 'disabled'])->default('active');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique('email', 'uq_admin_users_email');
            $table->foreign('role_id', 'fk_au_role')->references('id')->on('admin_roles')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
