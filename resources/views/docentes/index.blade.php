@extends('layouts.app')
@section('title', 'Docentes')
@section('subtitle', 'Gestión de docentes y profesores de tiempo completo')

@section('content')

{{-- Header acción --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-sm text-gray-400">{{ $docentes->count() }} docente(s) registrado(s)</p>
    </div>
    <a href="{{ route('docentes.create') }}"
       class="flex items-center gap-2 bg-vino-900 hover:bg-vino-800 text-white text-sm font-medium
              px-4 py-2.5 rounded-xl transition-all duration-150 shadow-sm hover:shadow-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo docente
    </a>
</div>

{{-- Tabla --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    @if($docentes->isEmpty())
        <div class="py-20 text-center">
            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-gray-400 font-medium">Sin docentes registrados</p>
            <p class="text-gray-300 text-sm mt-1">Agrega el primer docente para comenzar</p>
            <a href="{{ route('docentes.create') }}"
               class="inline-flex items-center gap-2 mt-4 bg-vino-900 text-white text-sm px-4 py-2 rounded-xl hover:bg-vino-800 transition-colors">
                Agregar docente
            </a>
        </div>
    @else
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Docente</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tipo</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Contacto</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Horarios</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($docentes as $docente)
                <tr class="hover:bg-gray-50/50 transition-colors duration-100 group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0
                                        {{ $docente->tipo === 'ptc' ? 'bg-vino-100 text-vino-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ strtoupper(substr($docente->nombre, 0, 1) . substr($docente->apellido_paterno, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $docente->nombre_completo }}</p>
                                <p class="text-xs text-gray-400">{{ $docente->email ?? 'Sin correo' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($docente->tipo === 'ptc')
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-vino-700 bg-vino-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 bg-vino-500 rounded-full"></span>
                                PTC
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 bg-gray-100 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                Asignatura
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell">
                        <p class="text-sm text-gray-500">{{ $docente->telefono ?? '—' }}</p>
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <span class="text-sm text-gray-500">
                            {{ $docente->horarios_count }}
                            {{ $docente->horarios_count === 1 ? 'día' : 'días' }} registrado(s)
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($docente->activo)
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                Activo
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                Inactivo
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('docentes.show', $docente) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-vino-700 hover:bg-vino-50 transition-colors"
                               title="Ver detalle">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('docentes.edit', $docente) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                               title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('docentes.destroy', $docente) }}"
                                  onsubmit="return confirm('¿Eliminar a {{ $docente->nombre_completo }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                    title="Eliminar">
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