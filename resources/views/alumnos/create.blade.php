@extends('layouts.app')
@section('title', 'Nuevo alumno')
@section('subtitle', 'Registrar alumno en proceso de titulación')

@section('content')
<div class="max-w-2xl">

    <a href="{{ route('alumnos.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-vino-700 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Regresar a alumnos
    </a>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7">
        <h2 class="text-base font-semibold text-gray-800 mb-6 pb-4 border-b border-gray-100">
            Datos del alumno
        </h2>

        <form method="POST" action="{{ route('alumnos.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Nombre(s) *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                           placeholder="Ej. Carlos Eduardo"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('nombre') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('nombre')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Apellido paterno *</label>
                    <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required
                           placeholder="Ej. Ramírez"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('apellido_paterno') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('apellido_paterno')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Apellido materno</label>
                    <input type="text" name="apellido_materno" value="{{ old('apellido_materno') }}"
                           placeholder="Ej. Torres"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all"/>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Matrícula *</label>
                    <input type="text" name="matricula" value="{{ old('matricula') }}" required
                           placeholder="Ej. 21100123"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all font-mono
                                  @error('matricula') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('matricula')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Modalidad *</label>
                    <select name="modalidad" required
                            class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                   @error('modalidad') border-red-300 bg-red-50 @else border-gray-200 @enderror">
                        <option value="">Seleccionar...</option>
                        <option value="tesis"                       {{ old('modalidad') === 'tesis' ? 'selected' : '' }}>Tesis</option>
                        <option value="residencia_profesional"      {{ old('modalidad') === 'residencia_profesional' ? 'selected' : '' }}>Residencia profesional</option>
                        <option value="proyecto_de_investigacion"   {{ old('modalidad') === 'proyecto_de_investigacion' ? 'selected' : '' }}>Proyecto de investigación</option>
                        <option value="memoria_de_experiencia"      {{ old('modalidad') === 'memoria_de_experiencia' ? 'selected' : '' }}>Memoria de experiencia</option>
                        <option value="titulacion_integral"         {{ old('modalidad') === 'titulacion_integral' ? 'selected' : '' }}>Titulación integral</option>
                    </select>
                    @error('modalidad')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Estado inicial *</label>
                    <select name="estado" required
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400">
                        <option value="en_proceso" selected>En proceso</option>
                        <option value="documentacion">Documentación</option>
                        <option value="asignado">Asignado</option>
                        <option value="titulado">Titulado</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('alumnos.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-gray-500 rounded-xl border border-gray-200 hover:border-gray-300 transition-all">
                    Cancelar
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-vino-900 hover:bg-vino-800 text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow-md">
                    Guardar alumno
                </button>
            </div>
        </form>
    </div>
</div>
@endsection