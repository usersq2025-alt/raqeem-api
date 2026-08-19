<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100);
            $table->text('setting_value')->nullable();
            $table->string('group_name', 50)->default('general');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique('setting_key', 'uq_settings_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
