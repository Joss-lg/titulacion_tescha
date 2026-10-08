<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('titulaciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('alumno_id')->constrained('alumnos');
        $table->foreignId('examen_id')->nullable()->constrained('examenes');

        // Datos del momento de la titulación (snapshot)
        // por si después se edita el alumno
        $table->string('nombre_completo');
        $table->string('matricula');
        $table->string('carrera')->default('Ingeniería en Sistemas Computacionales');
        $table->enum('modalidad', [
            'tesis',
            'residencia_profesional',
            'proyecto_de_investigacion',
            'memoria_de_experiencia',
            'titulacion_integral',
        ]);

        // Datos del examen
        $table->date('fecha_examen')->nullable();
        $table->time('hora_inicio')->nullable();
        $table->time('hora_fin')->nullable();
        $table->string('salon')->nullable();

        // Sinodales (snapshot)
        $table->string('presidente')->nullable();
        $table->string('secretario')->nullable();
        $table->string('vocal')->nullable();

        // Resultado
        $table->enum('resultado', ['aprobado', 'no_aprobado', 'aplazado'])->default('aprobado');
        $table->text('observaciones')->nullable();

        // Periodo escolar
        $table->string('periodo')->nullable(); // Ej: "Sep 2026 - Feb 2027"

        $table->date('fecha_registro')->default(now());
        $table->timestamps();
    });
}
};
