<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoTitulacion extends Model
{
    protected $table = 'documentos_titulacion';
    protected $fillable = [
        'alumno_id',
        'anexo_31', 'anexo_31_fecha',
        'anexo_32', 'anexo_32_fecha',
        'anexo_33', 'anexo_33_fecha',
        'hoja_asignacion', 'hoja_asignacion_fecha',
        'autorizacion_asesor', 'autorizacion_asesor_fecha',
        'anexo_plagio', 'anexo_plagio_fecha',
        'notas',
    ];

    protected $casts = [
        'anexo_31'           => 'boolean',
        'anexo_32'           => 'boolean',
        'anexo_33'           => 'boolean',
        'hoja_asignacion'    => 'boolean',
        'autorizacion_asesor'=> 'boolean',
        'anexo_plagio'       => 'boolean',
        'anexo_31_fecha'          => 'date',
        'anexo_32_fecha'          => 'date',
        'anexo_33_fecha'          => 'date',
        'hoja_asignacion_fecha'   => 'date',
        'autorizacion_asesor_fecha' => 'date',
        'anexo_plagio_fecha'      => 'date',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    // Cuántos documentos van entregados
    public function getTotalEntregadosAttribute(): int
    {
        return collect([
            $this->anexo_31,
            $this->anexo_32,
            $this->anexo_33,
            $this->hoja_asignacion,
            $this->autorizacion_asesor,
            $this->anexo_plagio,
        ])->filter()->count();
    }

    public function getEstaCompletoAttribute(): bool
    {
        return $this->total_entregados === 6;
    }
}