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
        'grado',
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
        $apellidos = trim("{$this->apellido_paterno} {$this->apellido_materno}");
        return "{$this->nombre} {$apellidos}";
    }

    public function getNombreConGradoAttribute(): string
    {
        $apellidos = trim("{$this->apellido_paterno} {$this->apellido_materno}");
        $grado = $this->grado ?? '';
        return trim("{$grado} {$this->nombre} {$apellidos}");
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