<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examenes', function (Blueprint $table) {
            $table->string('folio_oficio', 30)->nullable()->after('salon');
            $table->date('fecha_oficio')->nullable()->after('folio_oficio');
        });
    }

    public function down(): void
    {
        Schema::table('examenes', function (Blueprint $table) {
            $table->dropColumn(['folio_oficio', 'fecha_oficio']);
        });
    }
};