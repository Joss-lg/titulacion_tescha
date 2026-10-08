<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    protected $table = 'docentes';
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'tipo',
        'email',
        'telefono',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Nombre completo como atributo
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}";
    }

    public function esPtc(): bool
    {
        return $this->tipo === 'ptc';
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(HorarioDocente::class);
    }

    public function sinodales(): HasMany
    {
        return $this->hasMany(Sinodal::class);
    }

    // Examenes en los que participa
    public function examenes()
    {
        return $this->hasManyThrough(Examen::class, Sinodal::class, 'docente_id', 'id', 'id', 'examen_id');
    }
}