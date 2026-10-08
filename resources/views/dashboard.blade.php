@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- Saludo --}}
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800">
        Bienvenida, <span class="text-vino-900">{{ auth()->user()->name }}</span> 👋
    </h2>
    <p class="text-gray-400 text-sm mt-1">Aquí tienes un resumen del estado actual de titulación ISC.</p>
</div>

{{-- Tarjetas de estadísticas --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">

    {{-- Alumnos --}}
    <div class="stat-card group bg-white rounded-2xl shadow-sm border border-gray-100 p-6
                hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default overflow-hidden relative">
        <div class="absolute inset-0 bg-gradient-to-br from-vino-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 bg-vino-100 rounded-xl flex items-center justify-center
                            group-hover:bg-vino-900 transition-colors duration-300">
                    <svg class="w-5 h-5 text-vino-700 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-vino-400 bg-vino-50 px-2.5 py-1 rounded-full">Total</span>
            </div>
            <p class="stat-number text-4xl font-bold text-gray-800 mb-1" data-target="{{ $totalAlumnos }}">0</p>
            <p class="text-sm text-gray-400 font-medium">Alumnos en proceso</p>
        </div>
    </div>

    {{-- Exámenes --}}
    <div class="stat-card group bg-white rounded-2xl shadow-sm border border-gray-100 p-6
                hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default overflow-hidden relative">
        <div class="absolute inset-0 bg-gradient-to-br from-vino-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 bg-vino-100 rounded-xl flex items-center justify-center
                            group-hover:bg-vino-900 transition-colors duration-300">
                    <svg class="w-5 h-5 text-vino-700 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Programados</span>
            </div>
            <p class="stat-number text-4xl font-bold text-gray-800 mb-1" data-target="{{ $totalExamenes }}">0</p>
            <p class="text-sm text-gray-400 font-medium">Exámenes próximos</p>
        </div>
    </div>

    {{-- Docentes --}}
    <div class="stat-card group bg-white rounded-2xl shadow-sm border border-gray-100 p-6
                hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default overflow-hidden relative">
        <div class="absolute inset-0 bg-gradient-to-br from-vino-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 bg-vino-100 rounded-xl flex items-center justify-center
                            group-hover:bg-vino-900 transition-colors duration-300">
                    <svg class="w-5 h-5 text-vino-700 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">Activos</span>
            </div>
            <p class="stat-number text-4xl font-bold text-gray-800 mb-1" data-target="{{ $totalDocentes }}">0</p>
            <p class="text-sm text-gray-400 font-medium">Docentes registrados</p>
        </div>
    </div>

</div>

{{-- Fila inferior --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Accesos rápidos --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <span class="w-1.5 h-4 bg-vino-700 rounded-full inline-block"></span>
            Acciones rápidas
        </h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="#" class="quick-action flex items-center gap-3 p-3.5 rounded-xl border border-gray-100
                               hover:border-vino-200 hover:bg-vino-50 transition-all duration-200 group">
                <div class="w-8 h-8 bg-vino-100 rounded-lg flex items-center justify-center flex-shrink-0
                            group-hover:bg-vino-700 transition-colors duration-200">
                    <svg class="w-4 h-4 text-vino-700 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 group-hover:text-vino-800">Nuevo alumno</span>
            </a>
            <a href="#" class="quick-action flex items-center gap-3 p-3.5 rounded-xl border border-gray-100
                               hover:border-vino-200 hover:bg-vino-50 transition-all duration-200 group">
                <div class="w-8 h-8 bg-vino-100 rounded-lg flex items-center justify-center flex-shrink-0
                            group-hover:bg-vino-700 transition-colors duration-200">
                    <svg class="w-4 h-4 text-vino-700 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 group-hover:text-vino-800">Nuevo docente</span>
            </a>
            <a href="#" class="quick-action flex items-center gap-3 p-3.5 rounded-xl border border-gray-100
                               hover:border-vino-200 hover:bg-vino-50 transition-all duration-200 group">
                <div class="w-8 h-8 bg-vino-100 rounded-lg flex items-center justify-center flex-shrink-0
                            group-hover:bg-vino-700 transition-colors duration-200">
                    <svg class="w-4 h-4 text-vino-700 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 group-hover:text-vino-800">Programar examen</span>
            </a>
            <a href="#" class="quick-action flex items-center gap-3 p-3.5 rounded-xl border border-gray-100
                               hover:border-vino-200 hover:bg-vino-50 transition-all duration-200 group">
                <div class="w-8 h-8 bg-vino-100 rounded-lg flex items-center justify-center flex-shrink-0
                            group-hover:bg-vino-700 transition-colors duration-200">
                    <svg class="w-4 h-4 text-vino-700 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 group-hover:text-vino-800">Ver documentos</span>
            </a>
        </div>
    </div>

    {{-- Estado del sistema --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <span class="w-1.5 h-4 bg-vino-700 rounded-full inline-block"></span>
            Estado del sistema
        </h3>
        <div class="space-y-3.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                    <span class="text-sm text-gray-600">Base de datos</span>
                </div>
                <span class="text-xs text-emerald-600 font-medium bg-emerald-50 px-2 py-0.5 rounded-full">Conectada</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                    <span class="text-sm text-gray-600">Sistema</span>
                </div>
                <span class="text-xs text-emerald-600 font-medium bg-emerald-50 px-2 py-0.5 rounded-full">Operativo</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 bg-vino-400 rounded-full"></span>
                    <span class="text-sm text-gray-600">Laravel</span>
                </div>
                <span class="text-xs text-vino-700 font-medium bg-vino-50 px-2 py-0.5 rounded-full">v{{ app()->version() }}</span>
            </div>
            <div class="pt-2 border-t border-gray-50">
                <p class="text-xs text-gray-300">
                    Última actualización: {{ now()->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
    </div>

</div>

{{-- Animación contadores --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // Animación de entrada de tarjetas
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + i * 120);
    });

    // Animación contadores numéricos
    const counters = document.querySelectorAll('.stat-number');
    counters.forEach((el, i) => {
        const target = parseInt(el.dataset.target) || 0;
        let current = 0;
        const duration = 900;
        const step = Math.ceil(target / (duration / 16));

        setTimeout(() => {
            if (target === 0) { el.textContent = '0'; return; }
            const interval = setInterval(() => {
                current = Math.min(current + step, target);
                el.textContent = current;
                if (current >= target) clearInterval(interval);
            }, 16);
        }, 300 + i * 120);
    });

    // Animación acciones rápidas
    const actions = document.querySelectorAll('.quick-action');
    actions.forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'scale(0.95)';
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.opacity = '1';
            el.style.transform = 'scale(1)';
        }, 500 + i * 80);
    });

});
</script>
@endpush

@endsection