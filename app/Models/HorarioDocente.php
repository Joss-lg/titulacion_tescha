<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioDocente extends Model
{
    protected $table = 'horarios_docente';

    protected $fillable = [
        'docente_id',
        'dia',
        'hora_entrada',
        'hora_salida',
        'tiene_bloque_muerto',
        'bloque_muerto_inicio',
        'bloque_muerto_fin',
        'comida_inicio',
        'comida_fin',
    ];

    protected $casts = [
        'tiene_bloque_muerto' => 'boolean',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }

    // Verifica si una hora cae en bloque muerto
    public function estaEnBloqueMuerto(string $hora): bool
    {
        if (!$this->tiene_bloque_muerto) return false;

        return $hora >= $this->bloque_muerto_inicio
            && $hora <= $this->bloque_muerto_fin;
    }

    // Verifica si una hora cae en hora de comida (solo PTC)
    public function estaEnComida(string $hora): bool
    {
        if (!$this->comida_inicio) return false;

        return $hora >= $this->comida_inicio
            && $hora <= $this->comida_fin;
    }
}