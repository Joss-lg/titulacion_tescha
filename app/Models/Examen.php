<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examen extends Model
{
    protected $table = 'examenes';

    protected $fillable = [
        'fecha',
        'hora_inicio',
        'hora_fin',
        'salon',
        'folio_oficio',
        'fecha_oficio',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_oficio' => 'date',
    ];

    /**
     * Bloques de una hora para programar exámenes: ['07:00' => '07:00 a 08:00', ...].
     *
     * @return array<string, string>
     */
    public static function bloquesHorario(): array
    {
        $bloques = [];
        for ($hora = 7; $hora <= 20; $hora++) {
            $inicio = sprintf('%02d:00', $hora);
            $bloques[$inicio] = $inicio.' a '.sprintf('%02d:00', $hora + 1);
        }

        return $bloques;
    }

    /**
     * Alumnos que presentan el examen: uno o un equipo de 2 o 3.
     */
    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'alumno_examen')
            ->withTimestamps()
            ->orderBy('apellido_paterno');
    }

    /**
     * Presidente definido en los alumnos. En equipo, todos deben compartir el mismo;
     * si ninguno lo tiene o difieren, devuelve null.
     */
    public function presidenteDelEquipoId(): ?int
    {
        $ids = $this->alumnos->pluck('presidente_id')->unique();

        return $ids->count() === 1 && $ids->first() ? (int) $ids->first() : null;
    }

    /**
     * Iniciales para el avatar: las del alumno o el número de integrantes del equipo.
     */
    public function getInicialesAlumnosAttribute(): string
    {
        if ($this->alumnos->count() > 1) {
            return (string) $this->alumnos->count();
        }

        $alumno = $this->alumnos->first();

        return $alumno ? mb_strtoupper(mb_substr($alumno->nombre, 0, 1).mb_substr($alumno->apellido_paterno, 0, 1)) : '';
    }

    /**
     * Nombres de los sustentantes, p. ej. "Ana López, Beto Ruiz y Carla Díaz".
     */
    public function getNombresAlumnosAttribute(): string
    {
        $nombres = $this->alumnos->map(fn (Alumno $alumno) => trim($alumno->nombre_completo));

        return $nombres->count() > 1
            ? $nombres->slice(0, -1)->implode(', ').' y '.$nombres->last()
            : (string) $nombres->first();
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
