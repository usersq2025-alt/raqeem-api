<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_completion_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');
            $table->enum('reward_type', ['points', 'item']);
            $table->unsignedInteger('points_amount')->nullable();
            $table->unsignedBigInteger('store_item_id')->nullable();

            $table->unique('unit_id', 'uq_ucr_unit');
            $table->foreign('unit_id', 'fk_ucr_unit')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('store_item_id', 'fk_ucr_item')->references('id')->on('store_items')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_completion_rewards');
    }
};
