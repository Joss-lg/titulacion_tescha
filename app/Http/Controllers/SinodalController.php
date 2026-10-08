<?php

namespace App\Http\Controllers;

use App\Models\Examen;
use App\Models\Sinodal;
use App\Models\Docente;

class SinodalController extends Controller
{
    public function index()
    {
        // Todos los exámenes con sus sinodales
        $examenes = Examen::with('alumno', 'sinodales.docente')
            ->orderBy('fecha', 'desc')
            ->get();

        // Docentes más asignados
        $docentesMasActivos = Sinodal::with('docente')
            ->select('docente_id', \DB::raw('count(*) as total'))
            ->groupBy('docente_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $stats = [
            'total_examenes'    => $examenes->count(),
            'con_jurado'        => $examenes->filter(fn($e) => $e->sinodales->count() === 3)->count(),
            'sin_jurado'        => $examenes->filter(fn($e) => $e->sinodales->count() === 0)->count(),
            'jurado_incompleto' => $examenes->filter(fn($e) => $e->sinodales->count() > 0 && $e->sinodales->count() < 3)->count(),
        ];

        return view('sinodales.index', compact('examenes', 'stats', 'docentesMasActivos'));
    }
}