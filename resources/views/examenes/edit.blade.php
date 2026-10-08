@extends('layouts.app')
@section('title', 'Editar examen')
@section('subtitle', $examen->alumno->nombre_completo)

@section('content')
<div class="max-w-xl">

    <a href="{{ route('examenes.show', $examen->id) }}"
       class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-vino-700 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Regresar al examen
    </a>

    {{-- Alerta si ya tiene sinodales --}}
    @if($examen->sinodales->count() > 0)
    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 mb-5 flex items-start gap-3">
        <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-amber-700">Este examen ya tiene sinodales asignados</p>
            <p class="text-xs text-amber-600 mt-0.5">
                Si cambias la fecha u hora, verifica que los sinodales sigan disponibles en el nuevo horario.
            </p>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7">
        <h2 class="text-base font-semibold text-gray-800 mb-6 pb-4 border-b border-gray-100">
            Modificar datos del examen
        </h2>

        <form method="POST" action="{{ route('examenes.update', $examen->id) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Alumno (solo lectura) --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Alumno</label>
                <div class="flex items-center gap-3 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl">
                    <div class="w-7 h-7 rounded-lg bg-vino-100 text-vino-800 flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($examen->alumno->nombre, 0, 1) . substr($examen->alumno->apellido_paterno, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">{{ $examen->alumno->nombre_completo }}</p>
                        <p class="text-xs font-mono text-gray-400">{{ $examen->alumno->matricula }}</p>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">El alumno no se puede cambiar. Elimina el examen si necesitas reasignar.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Fecha del examen *
                    </label>
                    <input type="date" name="fecha"
                           value="{{ old('fecha', \Carbon\Carbon::parse($examen->fecha)->format('Y-m-d')) }}"
                           required
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('fecha') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('fecha')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Hora inicio *
                    </label>
                    <input type="time" name="hora_inicio"
                           value="{{ old('hora_inicio', \Carbon\Carbon::parse($examen->hora_inicio)->format('H:i')) }}"
                           required
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('hora_inicio') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('hora_inicio')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Hora fin *
                    </label>
                    <input type="time" name="hora_fin"
                           value="{{ old('hora_fin', \Carbon\Carbon::parse($examen->hora_fin)->format('H:i')) }}"
                           required
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('hora_fin') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('hora_fin')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Salón</label>
                    <input type="text" name="salon"
                           value="{{ old('salon', $examen->salon) }}"
                           placeholder="Ej. A-101"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all"/>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Estado</label>
                <select name="estado"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400">
                    <option value="programado" {{ old('estado', $examen->estado) === 'programado' ? 'selected' : '' }}>
                        Programado
                    </option>
                    <option value="realizado"  {{ old('estado', $examen->estado) === 'realizado'  ? 'selected' : '' }}>
                        Realizado
                    </option>
                    <option value="cancelado"  {{ old('estado', $examen->estado) === 'cancelado'  ? 'selected' : '' }}>
                        Cancelado
                    </option>
                </select>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    Última modificación: {{ $examen->updated_at->isoFormat('D [de] MMMM, YYYY [a las] H:mm') }}
                </p>
                <div class="flex gap-3">
                    <a href="{{ route('examenes.show', $examen->id) }}"
                       class="px-5 py-2.5 text-sm font-medium text-gray-500 rounded-xl border border-gray-200 hover:border-gray-300 transition-all">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-vino-900 hover:bg-vino-800 text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow-md">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection