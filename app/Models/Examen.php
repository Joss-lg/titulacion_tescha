<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examen extends Model
{
     protected $table = 'examenes';

    protected $fillable = [
        'alumno_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'salon',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function sinodales(): HasMany
    {
        return $this->hasMany(Sinodal::class);
    }

    public function presidente()
    {
        return $this->sinodales()->where('rol', 'presidente')->with('docente')->first();
    }

    public function secretario()
    {
        return $this->sinodales()->where('rol', 'secretario')->with('docente')->first();
    }

    public function vocal()
    {
        return $this->sinodales()->where('rol', 'vocal')->with('docente')->first();
    }

    // Dia de la semana en español para buscar horarios
    public function getDiaSemanaAttribute(): string
    {
        $dias = [
            0 => 'domingo',
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
        ];

        return $dias[$this->fecha->dayOfWeek];
    }

    public function getRouteKeyName(): string
{
    return 'id';
}
}