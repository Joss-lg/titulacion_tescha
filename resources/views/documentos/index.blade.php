@extends('layouts.app')
@section('title', 'Documentos')
@section('subtitle', 'Seguimiento de expedientes de titulación')

@section('content')

@php
$documentosList = [
    'anexo_31'            => 'Anexo 31',
    'anexo_32'            => 'Anexo 32',
    'anexo_33'            => 'Anexo 33',
    'hoja_asignacion'     => 'Hoja asig.',
    'autorizacion_asesor' => 'Autorización',
    'anexo_plagio'        => 'Plagio',
];
@endphp

{{-- Estadísticas --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-gray-600">{{ $stats['total'] }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500">Total alumnos</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-emerald-700">{{ $stats['completos'] }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500">Expediente completo</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-amber-700">{{ $stats['en_proceso'] }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500">En proceso</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-red-600">{{ $stats['sin_iniciar'] }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500">Sin documentos</p>
        </div>
    </div>

</div>

{{-- Tabla --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    @if($alumnos->isEmpty())
        <div class="py-20 text-center">
            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="text-gray-400 font-medium">Sin alumnos registrados</p>
        </div>
    @else

        {{-- Header de columnas --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Alumno</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Progreso</th>
                        @foreach($documentosList as $campo => $etiqueta)
                            <th class="px-3 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">
                                {{ $etiqueta }}
                            </th>
                        @endforeach
                        <th class="px-6 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($alumnos as $alumno)
                    @php
                        $docs       = $alumno->documentos;
                        $entregados = $docs ? $docs->total_entregados : 0;
                        $pct        = round(($entregados / 6) * 100);
                        $completo   = $docs?->esta_completo;
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors group">

                        {{-- Alumno --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0
                                            {{ $completo ? 'bg-emerald-100 text-emerald-700' : 'bg-vino-100 text-vino-800' }}">
                                    {{ strtoupper(substr($alumno->nombre, 0, 1) . substr($alumno->apellido_paterno, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">{{ $alumno->nombre_completo }}</p>
                                    <p class="text-xs font-mono text-gray-400">{{ $alumno->matricula }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Progreso --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all
                                        {{ $pct === 100 ? 'bg-emerald-400' : ($pct > 50 ? 'bg-amber-400' : 'bg-vino-400') }}"
                                        style="width: {{ $pct }}%">
                                    </div>
                                </div>
                                <span class="text-xs font-bold
                                    {{ $pct === 100 ? 'text-emerald-600' : ($pct > 50 ? 'text-amber-600' : 'text-vino-600') }}">
                                    {{ $entregados }}/6
                                </span>
                            </div>
                        </td>

                        {{-- Checkboxes por documento --}}
                        @foreach($documentosList as $campo => $etiqueta)
                        @php
                            $entregado  = $docs && $docs->$campo;
                            $fechaCampo = $campo . '_fecha';
                            $fecha      = $docs && $docs->$fechaCampo
                                ? $docs->$fechaCampo->format('d/m')
                                : null;
                        @endphp
                        <td class="px-3 py-4 text-center">
                            @if($entregado)
                                <div class="flex flex-col items-center gap-0.5">
                                    <div class="w-6 h-6 bg-emerald-100 rounded-lg flex items-center justify-center mx-auto">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    @if($fecha)
                                        <span class="text-xs text-gray-300">{{ $fecha }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="w-6 h-6 bg-gray-100 rounded-lg flex items-center justify-center mx-auto">
                                    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        @endforeach

                        {{-- Acciones --}}
                        <td class="px-6 py-4">
                            <a href="{{ route('alumnos.show', $alumno) }}"
                               class="opacity-0 group-hover:opacity-100 transition-opacity
                                      text-xs font-medium text-vino-600 hover:text-vino-800 whitespace-nowrap">
                                Ver expediente →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @endif
</div>

@endsection