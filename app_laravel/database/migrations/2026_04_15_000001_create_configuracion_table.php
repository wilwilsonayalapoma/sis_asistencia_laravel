<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('configuracion')) {
            Schema::create('configuracion', function (Blueprint $table) {
                $table->id();
                $table->string('clave', 120)->unique();
                $table->text('valor')->nullable();
                $table->dateTime('creado_el')->useCurrent();
                $table->dateTime('actualizado_el')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
