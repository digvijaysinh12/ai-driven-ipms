@props(['active', 'icon'])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-3 px-3 py-2.5 text-sm font-semibold bg-slate-900 text-white rounded-xl shadow-lg shadow-slate-900/10 transition-all duration-200'
            : 'flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-all duration-200 group';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($icon))
        <i data-lucide="{{ $icon }}" class="w-4 h-4 {{ ($active ?? false) ? 'text-white' : 'text-slate-400 group-hover:text-slate-900' }} transition-colors"></i>
    @endif
    <span>{{ $slot }}</span>
</a>
