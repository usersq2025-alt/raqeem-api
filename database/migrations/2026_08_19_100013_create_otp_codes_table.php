<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id');
            $table->string('code', 10);
            $table->enum('purpose', ['signup_verification', 'password_reset', 'account_recovery']);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['parent_id', 'purpose'], 'idx_otp_parent_purpose');
            $table->foreign('parent_id', 'fk_otp_parent')->references('id')->on('parents')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
