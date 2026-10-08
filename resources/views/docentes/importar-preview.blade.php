@extends('layouts.app')
@section('title', 'Confirmar importación')
@section('subtitle', 'Revisa los horarios extraídos antes de guardar')

@section('content')

<div class="max-w-3xl">

    {{-- Header info docente detectado --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-gray-800">PDF analizado correctamente</p>
            <p class="text-xs text-gray-400 mt-0.5">
                Docente detectado: <span class="font-medium text-gray-600">{{ $datos['docente']['nombre_completo'] ?? $docente->nombre_completo }}</span>
                · Periodo: <span class="font-medium text-gray-600">{{ $datos['docente']['periodo'] ?? '—' }}</span>
            </p>
        </div>
        <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-full">
            {{ count($datos['horarios']) }} días detectados
        </span>
    </div>

    {{-- Tabla de horarios --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-5">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <span class="w-1.5 h-4 bg-vino-700 rounded-full"></span>
            <h3 class="text-sm font-semibold text-gray-700">Horarios a importar</h3>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Día</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Entrada</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Salida</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Actividades</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($datos['horarios'] as $h)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-3">
                        <span class="text-xs font-bold text-vino-700 bg-vino-50 px-2.5 py-1 rounded-lg capitalize">
                            {{ $h['dia'] }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-700">{{ $h['hora_entrada'] }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-700">{{ $h['hora_salida'] }}</td>
                    <td class="px-6 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach(($h['actividades'] ?? []) as $act)
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $act }}</span>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Comida PTC --}}
    @if(!empty($datos['comida']['inicio']))
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-5 flex items-center gap-3">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-blue-700">Hora de comida detectada (PTC)</p>
            <p class="text-xs text-blue-500 mt-0.5">
                {{ $datos['comida']['inicio'] }} — {{ $datos['comida']['fin'] }}
                · Se aplicará a todos los días importados
            </p>
        </div>
    </div>
    @endif

    {{-- Advertencia si ya tiene horarios --}}
    @if($docente->horarios()->exists())
    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 mb-5 flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-sm text-amber-700">
            Este docente ya tiene horarios. Los días que ya existan no se duplicarán.
        </p>
    </div>
    @endif

    {{-- Botones --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('docentes.importar.form', $docente) }}"
           class="px-5 py-2.5 text-sm font-medium text-gray-500 rounded-xl border border-gray-200 hover:border-gray-300 transition-all">
            ← Subir otro PDF
        </a>
        <form method="POST" action="{{ route('docentes.importar.guardar', $docente) }}">
            @csrf
            <button type="submit"
                class="flex items-center gap-2 bg-vino-900 hover:bg-vino-800 text-white font-semibold
                       px-6 py-2.5 rounded-xl transition-all shadow-sm hover:shadow-md text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Confirmar e importar horarios
            </button>
        </form>
    </div>

</div>

@endsection