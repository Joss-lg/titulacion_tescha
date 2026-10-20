<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un examen puede presentarlo un alumno o un equipo de 2 o 3 alumnos.
     * Se mueve la relación examenes.alumno_id a una tabla pivote conservando los datos.
     */
    public function up(): void
    {
        Schema::create('alumno_examen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examen_id')->constrained('examenes')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['examen_id', 'alumno_id']);
        });

        DB::table('examenes')->whereNotNull('alumno_id')->orderBy('id')->each(function ($examen) {
            DB::table('alumno_examen')->insert([
                'examen_id' => $examen->id,
                'alumno_id' => $examen->alumno_id,
                'created_at' => $examen->created_at,
                'updated_at' => $examen->updated_at,
            ]);
        });

        Schema::table('examenes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('alumno_id');
        });
    }

    public function down(): void
    {
        Schema::table('examenes', function (Blueprint $table) {
            $table->foreignId('alumno_id')->nullable()->after('id')->constrained('alumnos')->cascadeOnDelete();
        });

        DB::table('alumno_examen')->orderBy('id')->each(function ($fila) {
            DB::table('examenes')
                ->where('id', $fila->examen_id)
                ->whereNull('alumno_id')
                ->update(['alumno_id' => $fila->alumno_id]);
        });

        Schema::dropIfExists('alumno_examen');
    }
};
