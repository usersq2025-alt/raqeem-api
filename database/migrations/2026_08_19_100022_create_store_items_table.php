<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_items', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['equipment', 'furniture']);
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('image_media_id')->nullable();
            $table->unsignedInteger('price_points');
            $table->enum('unlock_type', ['open', 'locked_visible', 'locked_hidden'])->default('open');
            $table->unsignedBigInteger('unlock_unit_id')->nullable()->comment('الوحدة التي يجب اجتيازها لكشف العنصر');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('category', 'idx_si_category');
            $table->index('unlock_unit_id', 'idx_si_unlock_unit');
            $table->foreign('image_media_id', 'fk_si_image')->references('id')->on('media_files')->onDelete('set null');
            $table->foreign('unlock_unit_id', 'fk_si_unlock_unit')->references('id')->on('units')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_items');
    }
};
