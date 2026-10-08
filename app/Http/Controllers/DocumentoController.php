<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\DocumentoTitulacion;

class DocumentoController extends Controller
{
    public function index()
    {
        $alumnos = Alumno::with('documentos')
            ->orderBy('apellido_paterno')
            ->get();

        // Estadísticas generales
        $stats = [
            'total'      => $alumnos->count(),
            'completos'  => $alumnos->filter(fn($a) => $a->documentos?->esta_completo)->count(),
            'en_proceso' => $alumnos->filter(fn($a) => $a->documentos && !$a->documentos->esta_completo && $a->documentos->total_entregados > 0)->count(),
            'sin_iniciar'=> $alumnos->filter(fn($a) => !$a->documentos || $a->documentos->total_entregados === 0)->count(),
        ];

        return view('documentos.index', compact('alumnos', 'stats'));
    }
}