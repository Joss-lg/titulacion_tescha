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
    Schema::create('alumnos', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('apellido_paterno');
        $table->string('apellido_materno')->nullable();
        $table->string('matricula')->unique();
        $table->string('carrera')->default('Ingeniería en Sistemas Computacionales');
        $table->enum('modalidad', [
            'tesis',
            'residencia_profesional',
            'proyecto_de_investigacion',
            'memoria_de_experiencia',
            'titulacion_integral'
        ]);
        $table->enum('estado', ['en_proceso', 'documentacion', 'asignado', 'titulado'])->default('en_proceso');
        $table->timestamps();
    });
}
};
