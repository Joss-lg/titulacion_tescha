@extends('layouts.app')
@section('title', 'Alumnos')
@section('subtitle', 'Control de alumnos en proceso de titulación')

@section('content')

@php
$estados = [
    'en_proceso'    => ['label' => 'En proceso',    'color' => 'blue'],
    'documentacion' => ['label' => 'Documentación', 'color' => 'amber'],
    'asignado'      => ['label' => 'Asignado',      'color' => 'purple'],
    'titulado'      => ['label' => 'Titulado',      'color' => 'emerald'],
];
@endphp

{{-- Resumen rápido --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach($estados as $key => $est)
    @php $total = $alumnos->where('estado', $key)->count(); @endphp
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
            {{ $est['color'] === 'blue'    ? 'bg-blue-100'    : '' }}
            {{ $est['color'] === 'amber'   ? 'bg-amber-100'   : '' }}
            {{ $est['color'] === 'purple'  ? 'bg-purple-100'  : '' }}
            {{ $est['color'] === 'emerald' ? 'bg-emerald-100' : '' }}">
            <span class="text-sm font-bold
                {{ $est['color'] === 'blue'    ? 'text-blue-700'    : '' }}
                {{ $est['color'] === 'amber'   ? 'text-amber-700'   : '' }}
                {{ $est['color'] === 'purple'  ? 'text-purple-700'  : '' }}
                {{ $est['color'] === 'emerald' ? 'text-emerald-700' : '' }}">
                {{ $total }}
            </span>
        </div>
        <p class="text-xs font-medium text-gray-500">{{ $est['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Header --}}
<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-gray-400">{{ $alumnos->count() }} alumno(s) registrado(s)</p>
    <a href="{{ route('alumnos.create') }}"
       class="flex items-center gap-2 bg-vino-900 hover:bg-vino-800 text-white text-sm font-medium
              px-4 py-2.5 rounded-xl transition-all shadow-sm hover:shadow-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo alumno
    </a>
</div>

{{-- Tabla --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    @if($alumnos->isEmpty())
        <div class="py-20 text-center">
            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
            </div>
            <p class="text-gray-400 font-medium">Sin alumnos registrados</p>
            <p class="text-gray-300 text-sm mt-1">Registra el primer alumno para comenzar</p>
            <a href="{{ route('alumnos.create') }}"
               class="inline-flex items-center gap-2 mt-4 bg-vino-900 text-white text-sm px-4 py-2 rounded-xl hover:bg-vino-800 transition-colors">
                Agregar alumno
            </a>
        </div>
    @else
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Alumno</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Matrícula</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Modalidad</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Documentos</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($alumnos as $alumno)
                @php
                    $docs      = $alumno->documentos;
                    $entregados = $docs ? $docs->total_entregados : 0;
                    $pct       = round(($entregados / 6) * 100);
                    $est       = $estados[$alumno->estado] ?? ['label' => $alumno->estado, 'color' => 'gray'];
                @endphp
                <tr class="hover:bg-gray-50/50 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-vino-100 text-vino-800 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($alumno->nombre, 0, 1) . substr($alumno->apellido_paterno, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $alumno->nombre_completo }}</p>
                                <p class="text-xs text-gray-400">ISC</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-mono font-medium text-gray-600">{{ $alumno->matricula }}</span>
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell">
                        <span class="text-xs text-gray-500">
                            {{ str_replace('_', ' ', ucfirst($alumno->modalidad)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden w-20">
                                <div class="h-full rounded-full transition-all duration-500
                                    {{ $pct === 100 ? 'bg-emerald-400' : ($pct > 50 ? 'bg-amber-400' : 'bg-vino-400') }}"
                                    style="width: {{ $pct }}%">
                                </div>
                            </div>
                            <span class="text-xs text-gray-400 font-medium">{{ $entregados }}/6</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $colorMap = [
                                'blue'    => 'text-blue-700 bg-blue-50',
                                'amber'   => 'text-amber-700 bg-amber-50',
                                'purple'  => 'text-purple-700 bg-purple-50',
                                'emerald' => 'text-emerald-700 bg-emerald-50',
                                'gray'    => 'text-gray-600 bg-gray-100',
                            ];
                        @endphp
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $colorMap[$est['color']] ?? 'text-gray-600 bg-gray-100' }}">
                            {{ $est['label'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('alumnos.show', $alumno) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-vino-700 hover:bg-vino-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('alumnos.edit', $alumno) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('alumnos.destroy', $alumno) }}"
                                  onsubmit="return confirm('¿Eliminar a {{ $alumno->nombre_completo }}?')">
                                @csrf @method('DELETE')
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection