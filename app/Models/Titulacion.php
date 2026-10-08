<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Titulacion extends Model
{
    protected $table = 'titulaciones';

protected $fillable = [
    'alumno_id',
    'examen_id',
    'nombre_completo',
    'matricula',
    'carrera',
    'modalidad',
    'fecha_examen',
    'hora_inicio',
    'hora_fin',
    'salon',
    'folio_oficio',
    'fecha_oficio',
    'presidente',
    'secretario',
    'vocal',
    'resultado',
    'observaciones',
    'periodo',
    'fecha_registro',
];

protected $casts = [
    'fecha_examen'   => 'date',
    'fecha_oficio'   => 'date',
    'fecha_registro' => 'date',
];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class);
    }
}