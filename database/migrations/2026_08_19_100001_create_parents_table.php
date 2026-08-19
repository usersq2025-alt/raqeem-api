<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 12)->comment('RQMP-XXXXXX');
            $table->string('full_name', 120);
            $table->string('email', 190);
            $table->string('phone_country_code', 6)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('password_hash', 255);
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('status', ['active', 'frozen', 'deleted'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique('public_id', 'uq_parents_public_id');
            $table->unique('email', 'uq_parents_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
