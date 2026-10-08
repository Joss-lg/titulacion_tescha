<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}";
    }

    public function examenes(): HasMany
    {
        return $this->hasMany(Examen::class);
    }

    // Último examen programado
    public function examenActual(): HasOne
    {
        return $this->hasOne(Examen::class)->latestOfMany();
    }

    public function documentos(): HasOne
    {
        return $this->hasOne(DocumentoTitulacion::class);
    }
}