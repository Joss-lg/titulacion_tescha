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
    Schema::create('horarios_docente', function (Blueprint $table) {
        $table->id();
        $table->foreignId('docente_id')->constrained('docentes')->cascadeOnDelete();
        $table->enum('dia', ['lunes','martes','miercoles','jueves','viernes','sabado']);
        $table->time('hora_entrada');
        $table->time('hora_salida');

        // Bloque muerto: hora que no le pagan, no puede ser sinodal
        $table->boolean('tiene_bloque_muerto')->default(false);
        $table->time('bloque_muerto_inicio')->nullable();
        $table->time('bloque_muerto_fin')->nullable();

        // Solo aplica a PTC
        $table->time('comida_inicio')->nullable();
        $table->time('comida_fin')->nullable();

        $table->timestamps();
    });
}
};
