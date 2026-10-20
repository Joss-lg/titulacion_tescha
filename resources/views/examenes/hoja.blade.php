@php
$meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fecha = $examen->fecha;
$fechaTexto = $fecha->day . ' de ' . $meses[$fecha->month] . ' de ' . $fecha->year;
$alumnos = $examen->alumnos;
$roles = ['presidente' => 'Presidente', 'secretario' => 'Secretario', 'vocal' => 'Vocal'];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoja de examen — {{ $examen->nombres_alumnos }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #1f2937; margin: 0; background: #f3f4f6; }
        .hoja { width: 21.59cm; min-height: 27.94cm; margin: 1.5rem auto; padding: 2cm; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.15); }
        .acciones { text-align: center; margin-top: 1.5rem; }
        .acciones button, .acciones a { font-size: 14px; padding: .5rem 1rem; border-radius: .5rem; border: 1px solid #d1d5db; background: #fff; color: #374151; text-decoration: none; cursor: pointer; margin: 0 .25rem; }
        .acciones button { background: #7f1d1d; color: #fff; border-color: #7f1d1d; }
        header { text-align: center; border-bottom: 2px solid #7f1d1d; padding-bottom: .75rem; margin-bottom: 1.5rem; }
        header h1 { font-size: 16px; margin: 0 0 .25rem; text-transform: uppercase; }
        header h2 { font-size: 14px; margin: 0; font-weight: normal; }
        h3 { font-size: 13px; text-transform: uppercase; color: #7f1d1d; margin: 1.5rem 0 .5rem; }
        table.datos { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.datos td { padding: .4rem .5rem; border: 1px solid #d1d5db; }
        table.datos td.etiqueta { width: 35%; background: #f9fafb; font-weight: bold; }
        .firmas { display: flex; justify-content: space-between; gap: 1.5rem; margin-top: 4rem; }
        .firma { flex: 1; text-align: center; font-size: 12px; }
        .firma .linea { border-top: 1px solid #111827; padding-top: .35rem; margin-top: 3rem; font-weight: bold; }
        .firma .rol { color: #6b7280; text-transform: uppercase; font-size: 11px; }
        @media print {
            body { background: #fff; }
            .hoja { margin: 0; box-shadow: none; width: auto; min-height: auto; padding: 0; }
            .acciones { display: none; }
            @page { size: letter; margin: 2cm; }
        }
    </style>
</head>
<body>
    <div class="acciones">
        <button type="button" onclick="window.print()">Imprimir</button>
        <a href="{{ route('examenes.show', $examen->id) }}">Volver al examen</a>
    </div>

    <div class="hoja">
        <header>
            <h1>Tecnológico de Estudios Superiores de Chalco</h1>
            <h2>Hoja de examen profesional</h2>
        </header>

        <h3>{{ $alumnos->count() > 1 ? 'Datos de los sustentantes' : 'Datos del sustentante' }}</h3>
        <table class="datos">
            @foreach($alumnos as $alumno)
            <tr><td class="etiqueta">Nombre</td><td>{{ $alumno->nombre_completo }}</td></tr>
            <tr><td class="etiqueta">Matrícula</td><td>{{ $alumno->matricula }}</td></tr>
            @endforeach
            <tr><td class="etiqueta">Carrera</td><td>{{ $alumnos->pluck('carrera')->unique()->implode(', ') }}</td></tr>
            <tr><td class="etiqueta">Opción de titulación</td><td>{{ str_replace('_', ' ', ucfirst($alumnos->pluck('modalidad')->unique()->implode(', '))) }}</td></tr>
        </table>

        <h3>Datos del examen</h3>
        <table class="datos">
            <tr><td class="etiqueta">Fecha</td><td>{{ $fechaTexto }}</td></tr>
            <tr><td class="etiqueta">Horario</td><td>{{ substr($examen->hora_inicio, 0, 5) }} a {{ substr($examen->hora_fin, 0, 5) }} hrs.</td></tr>
            <tr><td class="etiqueta">Lugar</td><td>{{ $examen->salon ?? 'Auditorio (Centro de Información)' }}</td></tr>
            @if($examen->folio_oficio)
            <tr><td class="etiqueta">Folio de oficio</td><td>{{ $examen->folio_oficio }}</td></tr>
            @endif
        </table>

        <h3>Jurado</h3>
        <table class="datos">
            @foreach($roles as $rol => $etiqueta)
                @php $sinodal = $examen->sinodales->firstWhere('rol', $rol); @endphp
                <tr>
                    <td class="etiqueta">{{ $etiqueta }}</td>
                    <td>{{ $sinodal?->docente->nombre_con_grado ?? 'Sin asignar' }}</td>
                </tr>
            @endforeach
        </table>

        <div class="firmas">
            @foreach($roles as $rol => $etiqueta)
                @php $sinodal = $examen->sinodales->firstWhere('rol', $rol); @endphp
                <div class="firma">
                    <div class="linea">{{ $sinodal?->docente->nombre_con_grado ?? '' }}</div>
                    <div class="rol">{{ $etiqueta }}</div>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>
