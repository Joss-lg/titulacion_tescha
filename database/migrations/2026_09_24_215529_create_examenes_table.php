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
    Schema::create('examenes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
        $table->date('fecha');
        $table->time('hora_inicio');
        $table->time('hora_fin');
        $table->string('salon')->nullable();
        $table->enum('estado', ['programado', 'realizado', 'cancelado'])->default('programado');
        $table->text('observaciones')->nullable();
        $table->timestamps();
    });
}
};
