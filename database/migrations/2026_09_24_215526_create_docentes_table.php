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
    Schema::create('docentes', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('apellido_paterno');
        $table->string('apellido_materno')->nullable();
        $table->enum('tipo', ['asignatura', 'ptc']); // PTC = Profesor de Tiempo Completo
        $table->string('email')->unique()->nullable();
        $table->string('telefono')->nullable();
        $table->boolean('activo')->default(true);
        $table->timestamps();
    });
}
};
