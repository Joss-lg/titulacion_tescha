<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sinodal extends Model
{
    protected $table = 'sinodales';
    
    protected $fillable = [
        'examen_id',
        'docente_id',
        'rol',
    ];

    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class);
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }
}