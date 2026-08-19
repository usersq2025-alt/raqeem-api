<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('code', 30);
            $table->string('name_ar', 60);
            $table->string('name_en', 60);

            $table->unique('code', 'uq_admin_roles_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_roles');
    }
};
