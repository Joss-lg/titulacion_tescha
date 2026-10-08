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
    Schema::create('documentos_titulacion', function (Blueprint $table) {
        $table->id();
        $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();

        // Checklist — true = entregado
        $table->boolean('anexo_31')->default(false);
        $table->date('anexo_31_fecha')->nullable();

        $table->boolean('anexo_32')->default(false);
        $table->date('anexo_32_fecha')->nullable();

        $table->boolean('anexo_33')->default(false);
        $table->date('anexo_33_fecha')->nullable();

        $table->boolean('hoja_asignacion')->default(false);
        $table->date('hoja_asignacion_fecha')->nullable();

        $table->boolean('autorizacion_asesor')->default(false);
        $table->date('autorizacion_asesor_fecha')->nullable();

        $table->boolean('anexo_plagio')->default(false);
        $table->date('anexo_plagio_fecha')->nullable();

        $table->text('notas')->nullable();
        $table->timestamps();
    });
}
};
