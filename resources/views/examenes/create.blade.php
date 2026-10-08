@extends('layouts.app')
@section('title', 'Programar examen')
@section('subtitle', 'Registrar fecha y hora de examen de titulación')

@section('content')
<div class="max-w-xl">

    <a href="{{ route('examenes.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-vino-700 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Regresar a exámenes
    </a>

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-5 flex items-start gap-3">
        <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-blue-600">
            Ingresa la fecha y hora que asignó <strong>Control Escolar</strong>. El sistema buscará automáticamente los docentes disponibles para esa fecha.
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7">
        <h2 class="text-base font-semibold text-gray-800 mb-6 pb-4 border-b border-gray-100">
            Datos del examen
        </h2>

        <form method="POST" action="{{ route('examenes.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Alumno *</label>
                <select name="alumno_id" required
                        class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                               @error('alumno_id') border-red-300 bg-red-50 @else border-gray-200 @enderror">
                    <option value="">Seleccionar alumno...</option>
                    @foreach($alumnos as $alumno)
                        <option value="{{ $alumno->id }}" {{ old('alumno_id') == $alumno->id ? 'selected' : '' }}>
                            {{ $alumno->nombre_completo }} — {{ $alumno->matricula }}
                        </option>
                    @endforeach
                </select>
                @error('alumno_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Fecha del examen *</label>
                    <input type="date" name="fecha" value="{{ old('fecha') }}" required
                           min="{{ now()->toDateString() }}"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('fecha') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('fecha')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Hora inicio *</label>
                    <input type="time" name="hora_inicio" value="{{ old('hora_inicio') }}" required
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('hora_inicio') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('hora_inicio')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Hora fin *</label>
                    <input type="time" name="hora_fin" value="{{ old('hora_fin') }}" required
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('hora_fin') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('hora_fin')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Salón</label>
                    <input type="text" name="salon" value="{{ old('salon') }}"
                           placeholder="Ej. A-101"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all"/>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('examenes.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-gray-500 rounded-xl border border-gray-200 hover:border-gray-300 transition-all">
                    Cancelar
                </a>
                <button type="submit"
                    class="flex items-center gap-2 px-6 py-2.5 bg-vino-900 hover:bg-vino-800 text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow-md">
                    Programar y asignar sinodales
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection