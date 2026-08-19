<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('store_item_id');
            $table->unsignedInteger('price_paid');
            $table->timestamp('purchased_at')->useCurrent();

            // قرار عمل نهائي: كل عنصر متجر يُشترى مرة واحدة فقط طوال عمر حساب الطالب
            $table->unique(['student_id', 'store_item_id'], 'uq_sp_student_item');
            $table->foreign('student_id', 'fk_sp_student')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('store_item_id', 'fk_sp_item')->references('id')->on('store_items')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_purchases');
    }
};
