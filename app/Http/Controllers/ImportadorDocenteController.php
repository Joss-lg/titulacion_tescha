<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\HorarioDocente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImportadorDocenteController extends Controller
{
    public function form(Docente $docente)
    {
        return view('docentes.importar', compact('docente'));
    }

public function procesar(Request $request, Docente $docente)
{
    $request->validate([
        'pdf' => 'required|file|mimes:pdf|max:10240',
    ]);

    $pdfBase64 = base64_encode(file_get_contents($request->file('pdf')->getRealPath()));
    $apiKey    = env('GEMINI_API_KEY');

    $modelos = [
        'gemini-3.8-flash',
        'gemini-3.5-flash',
        'gemini-3.1-flash-lite',
        'gemini-2.5-flash-lite',
        'gemini-flash-latest',      // alias genérico
        'gemini-pro-latest',        // fallback más pesado
    ];

    $prompt = <<<PROMPT
Analiza esta asignación académica del TESCHA y extrae los horarios del docente.
Devuelve ÚNICAMENTE un JSON válido sin texto adicional ni backticks:
{
  "docente": {
    "nombre_completo": "nombre del docente",
    "tipo": "ptc o asignatura",
    "periodo": "periodo académico"
  },
  "horarios": [
    {
      "dia": "lunes",
      "hora_entrada": "HH:MM",
      "hora_salida": "HH:MM",
      "actividades": ["materia 1", "materia 2"]
    }
  ],
  "comida": {
    "inicio": "HH:MM o null",
    "fin": "HH:MM o null"
  }
}
Reglas:
- Solo días donde SÍ trabaja (ignora celdas con 0)
- Consolida bloques del mismo día: entrada más temprana, salida más tardía
- "miércoles" → "miercoles" sin acento, igual "sábado" → "sabado"
- Horas en formato 24h HH:MM
- Si es PTC detecta hora de comida (hueco de 1hr)
PROMPT;

    $ultimoError = '';

    foreach ($modelos as $modelo) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}";

        try {
            $response = Http::timeout(30)->post($url, [
                'contents' => [[
                    'parts' => [
                        [
                            'inline_data' => [
                                'mime_type' => 'application/pdf',
                                'data'      => $pdfBase64,
                            ],
                        ],
                        ['text' => $prompt],
                    ],
                ]],
                'generationConfig' => [
                    'temperature'     => 0.1,
                    'maxOutputTokens' => 2000,
                    'thinkingConfig'  => ['thinkingBudget' => 0],
                ],
            ]);

            $status = $response->status();

            // Saturado o rate limit → siguiente modelo
            if (in_array($status, [429, 503])) {
                $ultimoError = "Modelo {$modelo} no disponible ({$status})";
                continue;
            }

            if (!$response->successful()) {
                $body = $response->json();
                // Si dice que el modelo no existe → siguiente
                if (isset($body['error']['code']) && $body['error']['code'] === 404) {
                    $ultimoError = "Modelo {$modelo} no encontrado";
                    continue;
                }
                $ultimoError = "Error {$modelo}: " . ($body['error']['message'] ?? $response->body());
                continue;
            }

            $content = $response->json('candidates.0.content.parts.0.text');

            if (!$content) {
                $ultimoError = "Respuesta vacía de {$modelo}";
                continue;
            }

            // Limpiar posibles backticks
            $content = preg_replace('/```json|```/i', '', $content);
            $content = trim($content);

            $datos = json_decode($content, true);

            if (!$datos || !isset($datos['horarios'])) {
                $ultimoError = "JSON inválido de {$modelo}";
                continue;
            }

            // Normalizar días
            foreach ($datos['horarios'] as &$h) {
                $h['dia'] = str_replace(
                    ['miércoles', 'Miércoles', 'sábado', 'Sábado',
                     'Lunes', 'Martes', 'Jueves', 'Viernes'],
                    ['miercoles', 'miercoles', 'sabado', 'sabado',
                     'lunes', 'martes', 'jueves', 'viernes'],
                    $h['dia']
                );
            }

            session([
                'importacion_horarios' => $datos,
                'modelo_usado'         => $modelo,
            ]);

            return view('docentes.importar-preview', compact('docente', 'datos'));

        } catch (\Exception $e) {
            $ultimoError = "Excepción {$modelo}: " . $e->getMessage();
            continue;
        }
    }

    // Todos fallaron
    return back()->with('error',
        'Los modelos de Gemini están saturados en este momento. ' .
        'Espera 2-3 minutos y vuelve a intentar. ' .
        '(Último error: ' . $ultimoError . ')'
    );
}

    public function guardar(Request $request, Docente $docente)
    {
        $datos = session('importacion_horarios');

        if (!$datos) {
            return redirect()->route('docentes.importar.form', $docente)
                ->with('error', 'La sesión expiró. Sube el PDF de nuevo.');
        }

        $guardados  = 0;
        $omitidos   = 0;

        foreach ($datos['horarios'] as $h) {
            if (empty($h['hora_entrada']) || empty($h['hora_salida'])) continue;

            // Evitar duplicados por día
            $existe = HorarioDocente::where('docente_id', $docente->id)
                ->where('dia', $h['dia'])
                ->exists();

            if ($existe) {
                $omitidos++;
                continue;
            }

            $horario = [
                'docente_id'          => $docente->id,
                'dia'                 => $h['dia'],
                'hora_entrada'        => $h['hora_entrada'],
                'hora_salida'         => $h['hora_salida'],
                'tiene_bloque_muerto' => false,
                'comida_inicio'       => null,
                'comida_fin'          => null,
            ];

            // Hora de comida solo para PTC
            if ($docente->tipo === 'ptc'
                && !empty($datos['comida']['inicio'])
                && $datos['comida']['inicio'] !== 'null') {
                $horario['comida_inicio'] = $datos['comida']['inicio'];
                $horario['comida_fin']    = $datos['comida']['fin'];
            }

            HorarioDocente::create($horario);
            $guardados++;
        }

        session()->forget('importacion_horarios');

        $msg = "Se importaron {$guardados} horario(s) correctamente.";
        if ($omitidos > 0) {
            $msg .= " ({$omitidos} día(s) ya existían y se omitieron)";
        }

        return redirect()->route('docentes.show', $docente)
            ->with('success', $msg);
    }
}