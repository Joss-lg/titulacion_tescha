<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Esta migración corre antes que la creación de "titulaciones" por su nombre de archivo.
     * En instalaciones nuevas la tabla aún no existe y las columnas se crean en
     * create_titulaciones_table; aquí solo se agregan en bases que ya tenían la tabla.
     */
    public function up(): void
    {
        if (! Schema::hasTable('titulaciones') || Schema::hasColumn('titulaciones', 'folio_oficio')) {
            return;
        }

        Schema::table('titulaciones', function (Blueprint $table) {
            $table->string('folio_oficio', 30)->nullable()->after('salon');
            $table->date('fecha_oficio')->nullable()->after('folio_oficio');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('titulaciones', 'folio_oficio')) {
            return;
        }

        Schema::table('titulaciones', function (Blueprint $table) {
            $table->dropColumn(['folio_oficio', 'fecha_oficio']);
        });
    }
};
