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
    Schema::create('sinodales', function (Blueprint $table) {
        $table->id();
        $table->foreignId('examen_id')->constrained('examenes')->cascadeOnDelete();
        $table->foreignId('docente_id')->constrained('docentes');
        $table->enum('rol', ['presidente', 'secretario', 'vocal']);
        $table->timestamps();

        // Un docente no puede tener dos roles en el mismo examen
        $table->unique(['examen_id', 'docente_id']);
        $table->unique(['examen_id', 'rol']);
    });
}
};
