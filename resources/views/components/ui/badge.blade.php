@props([
    'type' => 'neutral',
    'dot' => false,
])

@php
    $classes = match($type) {
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'error', 'danger' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'info' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        default => 'bg-zinc-50 text-zinc-600 ring-zinc-500/10',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold ring-1 ring-inset $classes"]) }}>
    @if($dot)
        <svg class="mr-1.5 h-1.5 w-1.5 fill-current" viewBox="0 0 6 6" aria-hidden="true">
            <circle cx="3" cy="3" r="3" />
        </svg>
    @endif
    {{ $slot }}
</span>
