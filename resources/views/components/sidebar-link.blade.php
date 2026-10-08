@props(['href', 'label'])

@php
    $active = request()->fullUrlIs($href) || request()->is(ltrim(parse_url($href, PHP_URL_PATH), '/') . '*');
@endphp

<a href="{{ $href }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group relative
          {{ $active ? 'text-white' : 'hover:bg-white/10' }}"
   style="{{ $active ? 'background: rgba(255,255,255,0.18); box-shadow: inset 0 1px 0 rgba(255,255,255,0.1);' : 'color: rgba(255,255,255,0.65);' }}">

    {{-- Indicador activo --}}
    @if($active)
        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-white rounded-r-full"></span>
    @endif

    {{-- Ícono --}}
    <span class="flex-shrink-0 {{ $active ? 'text-white' : 'text-white/50 group-hover:text-white/80' }} transition-colors">
        {{ $slot }}
    </span>

    {{-- Label --}}
    <span class="{{ $active ? 'text-white' : 'group-hover:text-white/90' }} transition-colors">
        {{ $label }}
    </span>
</a>