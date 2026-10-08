<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — @yield('title', 'Inicio')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="bg-gray-50 font-sans antialiased">

<div class="flex h-screen overflow-hidden">

    {{-- ══════════════════ SIDEBAR ══════════════════ --}}
    <aside id="sidebar" class="w-[260px] bg-[#F8F0F1] backdrop-blur-2xl border-r border-vino-100/60 flex flex-col justify-between z-50 shrink-0 shadow-[4px_0_24px_rgba(114,47,55,0.05)] overflow-x-hidden relative">

        <style>
            #sidebar { transition: width 0.4s cubic-bezier(0.25, 1, 0.5, 1); }
            .sidebar-text { transition: opacity 0.2s ease, width 0.3s ease, margin 0.3s ease; white-space: nowrap; overflow: hidden; }

            #sidebar.colapsado { width: 88px !important; }
            #sidebar.colapsado .sidebar-text { width: 0 !important; opacity: 0 !important; margin: 0 !important; pointer-events: none; }
            #sidebar.colapsado .menu-link { padding-left: 0 !important; padding-right: 0 !important; justify-content: center !important; }
            #sidebar.colapsado .logo-wrapper { opacity: 0; pointer-events: none; position: absolute; }
            #sidebar.colapsado #toggleSidebar { right: 0; left: 0; margin: 0 auto; top: 50%; transform: translateY(-50%); }
            #sidebar.colapsado .user-footer { padding-left: 0; padding-right: 0; background: transparent; border-color: transparent; box-shadow: none; align-items: center; margin-bottom: 1rem; }
            #sidebar.colapsado .btn-logout { width: 44px !important; height: 44px !important; padding: 0 !important; justify-content: center !important; }

            @media (max-width: 1023.98px) {
                #sidebar {
                    position: fixed; top: 0; left: 0; bottom: 0;
                    width: 84vw; max-width: 300px;
                    transform: translateX(-100%);
                    transition: transform 0.35s cubic-bezier(0.25, 1, 0.5, 1);
                }
                #sidebar.colapsado { width: 84vw; max-width: 300px; }
                #sidebar.colapsado .sidebar-text { width: auto !important; opacity: 1 !important; margin: 0 0 0 0.75rem !important; pointer-events: auto; }
                #sidebar.colapsado .logo-wrapper { opacity: 1; pointer-events: auto; position: relative; }
                #sidebar.colapsado .menu-link { padding-left: 0.75rem !important; padding-right: 0.75rem !important; justify-content: flex-start !important; }
                #sidebar.abierto { transform: translateX(0); }
                #sidebarOverlay {
                    display: none; position: fixed; inset: 0;
                    background: rgba(15, 23, 42, 0.4);
                    backdrop-filter: blur(2px); z-index: 40;
                    opacity: 0; transition: opacity 0.3s ease;
                }
                #sidebarOverlay.visible { display: block; opacity: 1; }
            }

            @keyframes slideInRight {
                from { opacity: 0; transform: translateX(-15px); }
                to   { opacity: 1; transform: translateX(0); }
            }
            .animate-slide-in { animation: slideInRight 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }

            #nav-container::-webkit-scrollbar { width: 4px; }
            #nav-container::-webkit-scrollbar-track { background: transparent; }
            #nav-container::-webkit-scrollbar-thumb { background: #d4a0a6; border-radius: 10px; }
            #nav-container:hover::-webkit-scrollbar-thumb { background: #a0404a; }
        </style>

        <script>
            (function() {
                if (window.matchMedia('(min-width: 1024px)').matches && localStorage.getItem('sidebarState') === 'collapsed') {
                    document.getElementById('sidebar').classList.add('colapsado');
                }
            })();
        </script>

        {{-- Header Logo --}}
        <div class="h-24 flex items-center px-5 relative shrink-0 w-full">
            <div class="absolute top-1/2 left-10 -translate-y-1/2 w-20 h-20 bg-vino-300/20 blur-[25px] rounded-full pointer-events-none"></div>

            <div class="logo-wrapper flex items-center relative z-10 w-full transition-opacity duration-300">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-vino-800 to-vino-950 p-[1px] shadow-[0_0_15px_rgba(114,47,55,0.3)] shrink-0 group hover:scale-105 transition-transform duration-300 cursor-pointer">
                    <div class="w-full h-full bg-white rounded-[11px] flex items-center justify-center">
                        <span class="text-vino-900 font-black text-xs group-hover:rotate-12 transition-transform duration-300 inline-block">ISC</span>
                    </div>
                </div>
                <div class="sidebar-text ml-3 flex flex-col">
                    <span class="font-black tracking-[0.1em] text-[15px] text-vino-900 leading-none">Titulación ISC</span>
                    <span class="text-[9px] text-vino-500 font-bold uppercase tracking-[0.25em] mt-1.5">TESCHA · Sistema interno</span>
                </div>
            </div>

            <button id="toggleSidebar"
                class="absolute right-4 top-7 w-8 h-8 flex items-center justify-center rounded-lg
                       bg-white hover:bg-vino-50 text-vino-400 hover:text-vino-700
                       transition-colors cursor-pointer z-50 shrink-0
                       border border-vino-100/60 hover:border-vino-200 shadow-sm">
                <i class="fas fa-bars text-sm"></i>
            </button>
        </div>

        {{-- Navegación --}}
        <nav class="py-4 px-3 space-y-5 overflow-y-auto overflow-x-hidden max-h-[calc(100vh-14rem)] relative z-10 flex-1" id="nav-container">

            @php
                $menu = [
                    'Principal' => [
                        ['route' => 'dashboard',        'icon' => 'fas fa-th-large',      'label' => 'Dashboard'],
                    ],
                    'Catálogos' => [
                        ['route' => 'docentes.index',   'icon' => 'fas fa-chalkboard-teacher', 'label' => 'Docentes'],
                        ['route' => 'alumnos.index',    'icon' => 'fas fa-user-graduate',  'label' => 'Alumnos'],
                    ],
                    'Titulación' => [
                        ['route' => 'examenes.index',   'icon' => 'fas fa-calendar-check', 'label' => 'Exámenes'],
                        ['route' => 'sinodales.index',  'icon' => 'fas fa-users',          'label' => 'Sinodales'],
                        ['route' => 'documentos.index', 'icon' => 'fas fa-clipboard-list', 'label' => 'Documentos'],
                    ],

                    'Historial' => [
                    ['route' => 'historial.index', 'icon' => 'fas fa-graduation-cap', 'label' => 'Historial'],
                    ],
                ];
                $delay = 0;
            @endphp

            @foreach($menu as $titulo => $items)
            <div class="space-y-1">
                <div class="px-3 pt-3 pb-1">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] sidebar-text text-vino-400/70">
                        {{ $titulo }}
                    </span>
                </div>

                @foreach($items as $item)
                @php
                    try {
                        $url = route($item['route']);
                        $isActive = request()->routeIs(str_replace('.index', '.*', $item['route']))
                                 || request()->routeIs($item['route']);
                    } catch (Exception $e) {
                        $url = '#';
                        $isActive = false;
                    }
                    $delay += 50;
                @endphp

                <a href="{{ $url }}"
                   class="menu-link animate-slide-in relative flex items-center px-3 py-2.5 rounded-xl transition-all duration-300 group overflow-hidden
                          {{ $isActive
                              ? 'bg-vino-50/80 border border-vino-100/50 shadow-sm'
                              : 'border border-transparent hover:bg-white hover:shadow-sm hover:translate-x-1' }}"
                   style="animation-delay: {{ $delay }}ms;">

                    {{-- Indicador activo --}}
                    @if($isActive)
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[4px] h-[50%] bg-vino-700 rounded-r-full shadow-[0_0_8px_rgba(114,47,55,0.5)]"></div>
                    @endif

                    {{-- Ícono --}}
                    <div class="menu-icon flex h-9 w-9 items-center justify-center rounded-[10px] transition-all duration-300 shrink-0 relative z-10
                                {{ $isActive
                                    ? 'bg-gradient-to-br from-vino-700 to-vino-900 text-white shadow-md shadow-vino-500/20'
                                    : 'bg-white border border-slate-200/80 text-slate-400 group-hover:text-vino-600 group-hover:border-vino-200 group-hover:bg-vino-50/50' }}">
                        <i class="{{ $item['icon'] }} text-[14px]"></i>
                    </div>

                    {{-- Label --}}
                    <span class="sidebar-text ml-3 text-[13.5px] tracking-wide
                                 {{ $isActive
                                     ? 'text-vino-800 font-bold'
                                     : 'text-slate-600 font-medium group-hover:text-vino-700' }}">
                        {{ $item['label'] }}
                    </span>
                </a>
                @endforeach
            </div>
            @endforeach

        </nav>

        {{-- Footer usuario --}}
        <div class="user-footer p-4 mx-3 mb-5 mt-2 rounded-2xl bg-white border border-vino-100/60
                    flex flex-col gap-3 shrink-0 relative transition-all duration-300 shadow-sm hover:shadow-md">
            <div class="flex items-center w-full">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-vino-800 to-vino-950
                            text-white flex items-center justify-center font-black text-sm shrink-0 shadow-md mx-auto">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="sidebar-text ml-3 flex flex-col justify-center min-w-0">
                    <p class="text-[13px] font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-vino-500 uppercase tracking-widest font-black mt-0.5">Administrador</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-1 w-full flex justify-center">
                @csrf
                <button type="submit"
                    class="btn-logout w-full h-[40px] px-3 flex items-center
                           bg-gradient-to-r from-vino-800 to-vino-950
                           hover:from-vino-700 hover:to-vino-900
                           text-white rounded-xl transition-all duration-300
                           shadow-md shadow-vino-900/20 hover:shadow-vino-900/40
                           active:scale-95 group overflow-hidden border border-vino-700/50">
                    <div class="flex items-center justify-center shrink-0 w-6">
                        <i class="fas fa-sign-out-alt text-[14px] transition-transform group-hover:translate-x-1"></i>
                    </div>
                    <span class="sidebar-text ml-2 text-[11px] font-bold tracking-widest mt-[1px]">CERRAR SESIÓN</span>
                </button>
            </form>
        </div>

    </aside>

    {{-- Overlay móvil --}}
    <div id="sidebarOverlay"></div>

    {{-- ══════════════════ CONTENIDO ══════════════════ --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Topbar --}}
        <header class="bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between shadow-sm flex-shrink-0">
            {{-- Botón hamburguesa móvil --}}
            <div class="flex items-center gap-4">
                <button id="mobileMenuBtn"
                    class="lg:hidden w-8 h-8 flex items-center justify-center rounded-lg text-vino-600 hover:bg-vino-50 transition-colors">
                    <i class="fas fa-bars text-sm"></i>
                </button>
                <div>
                    <h1 class="text-base font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-400 mt-0.5">@yield('subtitle', 'Sistema de titulación ISC · TESCHA')</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-300 hidden sm:block">
                    {{ now()->isoFormat('dddd D [de] MMMM, YYYY') }}
                </span>
                <div class="w-px h-5 bg-gray-100 hidden sm:block"></div>
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-vino-800 to-vino-950
                            flex items-center justify-center text-white text-xs font-bold shadow-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
            </div>
        </header>

        {{-- Área scrollable --}}
        <main class="flex-1 overflow-y-auto p-6">

            @if(session('success'))
                <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                    <i class="fas fa-times-circle text-red-500"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

    </div>
</div>

@stack('scripts')

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar    = document.getElementById('sidebar');
    const toggleBtn  = document.getElementById('toggleSidebar');
    const navContainer = document.getElementById('nav-container');
    const overlay    = document.getElementById('sidebarOverlay');
    const mobileBtn  = document.getElementById('mobileMenuBtn');
    const esMovil    = () => window.matchMedia('(max-width: 1023.98px)').matches;

    // Restaurar scroll
    if (navContainer) {
        const saved = localStorage.getItem('sidebarScrollPosition');
        if (saved) navContainer.scrollTop = saved;
        navContainer.addEventListener('scroll', () => {
            localStorage.setItem('sidebarScrollPosition', navContainer.scrollTop);
        });
    }

    function abrirDrawer() {
        sidebar.classList.add('abierto');
        overlay.classList.add('visible');
        document.body.style.overflow = 'hidden';
    }

    function cerrarDrawer() {
        sidebar.classList.remove('abierto');
        overlay.classList.remove('visible');
        document.body.style.overflow = '';
    }

    mobileBtn?.addEventListener('click', abrirDrawer);
    overlay?.addEventListener('click', cerrarDrawer);

    document.querySelectorAll('#nav-container .menu-link').forEach(link => {
        link.addEventListener('click', () => { if (esMovil()) cerrarDrawer(); });
    });

    toggleBtn?.addEventListener('click', () => {
        if (esMovil()) { cerrarDrawer(); return; }
        sidebar.classList.toggle('colapsado');
        localStorage.setItem('sidebarState', sidebar.classList.contains('colapsado') ? 'collapsed' : 'expanded');
    });

    window.addEventListener('resize', () => { if (!esMovil()) cerrarDrawer(); });
});
</script>

</body>
</html>