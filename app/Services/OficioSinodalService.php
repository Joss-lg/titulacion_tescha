<?php

namespace App\Services;

use App\Models\Examen;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\IOFactory;
use Carbon\Carbon;

class OficioSinodalService
{
    // Meses en español
    private array $meses = [
        1  => 'enero',   2  => 'febrero',  3  => 'marzo',
        4  => 'abril',   5  => 'mayo',     6  => 'junio',
        7  => 'julio',   8  => 'agosto',   9  => 'septiembre',
        10 => 'octubre', 11 => 'noviembre',12 => 'diciembre',
    ];

    // Opciones de titulación → texto legible
    private array $modalidades = [
        'tesis'                    => 'Tesis',
        'residencia_profesional'   => 'Residencia Profesional',
        'proyecto_de_investigacion'=> 'Proyecto de Investigación',
        'memoria_de_experiencia'   => 'Memoria de Experiencia Profesional',
        'titulacion_integral'      => 'Titulación Integral',
    ];

    public function generar(Examen $examen): string
    {
        $examen->load('alumno', 'sinodales.docente');

        $alumno    = $examen->alumno;
        $fecha     = Carbon::parse($examen->fecha);
        $dia       = $fecha->day;
        $mes       = $this->meses[$fecha->month];
        $anio      = $fecha->year;
        $horaInicio = substr($examen->hora_inicio, 0, 5); // HH:MM
        $salon     = $examen->salon ?? 'Auditorio';
        $modalidad = $this->modalidades[$alumno->modalidad] ?? $alumno->modalidad;

        // Sinodales por rol
        $presidente = $examen->sinodales->firstWhere('rol', 'presidente');
        $secretario = $examen->sinodales->firstWhere('rol', 'secretario');
        $vocal      = $examen->sinodales->firstWhere('rol', 'vocal');

        $nomPresidente = $presidente ? $presidente->docente->nombre_con_grado : '___________________';
        $nomSecretario = $secretario ? $secretario->docente->nombre_con_grado : '___________________';
        $nomVocal      = $vocal      ? $vocal->docente->nombre_con_grado      : '___________________';

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        // Estilos reutilizables
        $phpWord->addParagraphStyle('centrado', ['alignment' => Jc::CENTER]);
        $phpWord->addParagraphStyle('justificado', ['alignment' => Jc::BOTH, 'spaceAfter' => 120]);
        $phpWord->addParagraphStyle('sinEspacio', ['spaceAfter' => 0, 'spaceBefore' => 0]);

        $section = $phpWord->addSection([
            'marginTop'    => 720,  // 1.25cm
            'marginBottom' => 720,
            'marginLeft'   => 1080, // 1.9cm
            'marginRight'  => 1080,
        ]);

        // ── LEYENDA AÑO ──────────────────────────────────────────────────
        $section->addText(
            "{$anio}. Año del Humanismo Mexicano en el Estado de México",
            ['bold' => true, 'size' => 9],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 40]
        );

        $section->addTextBreak(1);

        // ── TÍTULO ───────────────────────────────────────────────────────
        $section->addText(
            'Asignación de Sinodales de Titulación Integral',
            ['bold' => true, 'size' => 13],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 80]
        );

        // ── NÚMERO DE OFICIO + FECHA ──────────────────────────────────────
        $numOficio = $examen->folio_oficio ?? ('ISC/' . $anio . '/' . str_pad($examen->id, 3, '0', STR_PAD_LEFT));

        // Usar fecha del oficio si existe, si no la fecha del examen
        $fechaOficioTexto = $dia . ' de ' . $mes . ' de ' . $anio;
        if ($examen->fecha_oficio) {
            $fo = Carbon::parse($examen->fecha_oficio);
            $fechaOficioTexto = $fo->day . ' de ' . $this->meses[$fo->month] . ' de ' . $fo->year;
        }

        $section->addText(
            $numOficio,
            ['size' => 11],
            ['alignment' => Jc::LEFT, 'spaceAfter' => 20]
        );
        $section->addText(
            "Chalco, Estado de México a {$fechaOficioTexto}",
            ['size' => 11],
            ['alignment' => Jc::LEFT, 'spaceAfter' => 200]
        );

        // ── DESTINATARIOS ─────────────────────────────────────────────────
        $section->addText('P R E S E N T E S', ['bold' => true, 'size' => 11], ['spaceAfter' => 40]);

        $destinatarios = [
            ['label' => 'SINODAL 1 (Presidente)', 'nombre' => strtoupper($nomPresidente)],
            ['label' => 'SINODAL 2 (Secretario)', 'nombre' => strtoupper($nomSecretario)],
            ['label' => 'SINODAL 3 (Vocal)',       'nombre' => strtoupper($nomVocal)],
        ];

        foreach ($destinatarios as $d) {
            $textRun = $section->addTextRun(['spaceAfter' => 20]);
            $textRun->addText($d['label'] . ': ', ['bold' => true, 'size' => 11]);
            $textRun->addText($d['nombre'], ['size' => 11]);
        }

        $section->addTextBreak(1);

        // ── CUERPO ────────────────────────────────────────────────────────
        $nombreAlumno  = strtoupper(trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}"));
        $matricula     = $alumno->matricula;
        $carrera       = $alumno->carrera;

        $cuerpo = "Por medio del presente, me permito comunicarles que han sido designados como sinodales para el examen de titulación del (la) alumno(a) {$nombreAlumno}, con número de matrícula {$matricula}, de la carrera de {$carrera}, bajo la modalidad de {$modalidad}.";

        $section->addText($cuerpo, ['size' => 11], ['alignment' => Jc::BOTH, 'spaceAfter' => 160]);

        // ── DATOS DEL EXAMEN ─────────────────────────────────────────────
        $section->addText(
            "El examen se llevará a cabo el día {$dia} de {$mes} de {$anio} a las {$horaInicio} horas en {$salon}.",
            ['size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 200]
        );

        // ── ROLES ─────────────────────────────────────────────────────────
        $section->addText('Los roles asignados son los siguientes:', ['size' => 11], ['spaceAfter' => 60]);

        $roles = [
            'Presidente' => $nomPresidente,
            'Secretario' => $nomSecretario,
            'Vocal'      => $nomVocal,
        ];
        foreach ($roles as $rol => $nombre) {
            $textRun = $section->addTextRun(['spaceAfter' => 40]);
            $textRun->addText("{$rol}: ", ['bold' => true, 'size' => 11]);
            $textRun->addText($nombre, ['size' => 11]);
        }

        $section->addTextBreak(2);

        // ── FIRMA ─────────────────────────────────────────────────────────
        $section->addText('A t e n t a m e n t e', ['size' => 11], ['alignment' => Jc::CENTER, 'spaceAfter' => 600]);

        $section->addText(
            'Dra. Fabiola Orquídea Sánchez Hernández',
            ['bold' => true, 'size' => 11],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        $section->addText(
            'Jefa de la División de Ingeniería en Sistemas Computacionales',
            ['size' => 11],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        $section->addText(
            'TESCHA',
            ['size' => 11],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        // ── CCP ───────────────────────────────────────────────────────────
        $section->addText('C.c.p.:', ['bold' => true, 'size' => 10], ['spaceAfter' => 20]);
        $ccps = [
            $nomPresidente . ' — Presidente',
            $nomSecretario . ' — Secretario',
            $nomVocal      . ' — Vocal',
            $nombreAlumno  . ' — Alumno(a)',
            'Archivo',
        ];
        foreach ($ccps as $ccp) {
            $section->addText("• {$ccp}", ['size' => 10], ['spaceAfter' => 10]);
        }

        // ── GUARDAR ARCHIVO ───────────────────────────────────────────────
        $nombreArchivo = 'oficio_sinodales_' . $alumno->matricula . '_' . $examen->id . '.docx';
        $ruta = storage_path('app/oficios/' . $nombreArchivo);

        if (!is_dir(storage_path('app/oficios'))) {
            mkdir(storage_path('app/oficios'), 0755, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($ruta);

        return $ruta;
    }
}