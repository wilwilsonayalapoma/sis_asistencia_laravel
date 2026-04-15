<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear tabla turno
        Schema::create('turno', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique();
            $table->time('hora_entrada')->comment('Hora oficial de entrada');
            $table->time('hora_tardanza')->comment('Hora límite para evitar tardanza');
            $table->time('hora_salida')->nullable()->comment('Hora esperada de salida (informativo)');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->index('estado');
        });

        // Insertar turnos por defecto
        DB::table('turno')->insert([
            ['nombre' => 'Mañana', 'hora_entrada' => '08:00:00', 'hora_tardanza' => '08:30:00', 'hora_salida' => '16:30:00', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tarde', 'hora_entrada' => '14:00:00', 'hora_tardanza' => '14:30:00', 'hora_salida' => '22:30:00', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Noche', 'hora_entrada' => '22:00:00', 'hora_tardanza' => '22:30:00', 'hora_salida' => '06:30:00', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Flexible', 'hora_entrada' => '07:00:00', 'hora_tardanza' => '09:00:00', 'hora_salida' => null, 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Agregar turno_id a asignacion_oficina
        Schema::table('asignacion_oficina', function (Blueprint $table) {
            if (!Schema::hasColumn('asignacion_oficina', 'turno_id')) {
                $table->unsignedBigInteger('turno_id')->default(1)->after('tipo_personal_id')->comment('Referencia al turno asignado');
                $table->foreign('turno_id')->references('id')->on('turno')->onUpdate('cascade')->onDelete('restrict');
                $table->index('turno_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asignacion_oficina', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['turno_id']);
            $table->dropIndexIfExists('asignacion_oficina_turno_id_index');
            $table->dropColumnIfExists('turno_id');
        });

        Schema::dropIfExists('turno');
    }
};
