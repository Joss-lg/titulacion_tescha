<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Alumno extends Model
{
    protected $table = 'alumnos';

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'matricula',
        'carrera',
        'modalidad',
        'estado',
        'presidente_id',
    ];

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}";
    }

    /**
     * Presidente del jurado, definido desde el registro del alumno.
     */
    public function presidente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'presidente_id');
    }

    /**
     * Exámenes que presenta el alumno, solo o en equipo.
     */
    public function examenes(): BelongsToMany
    {
        return $this->belongsToMany(Examen::class, 'alumno_examen')->withTimestamps();
    }

    /**
     * Último examen registrado del alumno.
     */
    public function getExamenActualAttribute(): ?Examen
    {
        return $this->examenes->sortByDesc('id')->first();
    }

    public function documentos(): HasOne
    {
        return $this->hasOne(DocumentoTitulacion::class);
    }
}
