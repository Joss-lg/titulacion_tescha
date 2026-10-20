<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El presidente del jurado se define desde que se registra al alumno.
     * Es nullable para los alumnos que ya existían antes de este cambio.
     */
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreignId('presidente_id')->nullable()->after('estado')->constrained('docentes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('presidente_id');
        });
    }
};
