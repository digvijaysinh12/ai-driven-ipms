@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden group']) }}>
    @if($title || isset($header))
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
            <div>
                @if($title)
                    <h3 class="text-sm font-bold text-zinc-900 tracking-tight">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-[11px] text-zinc-500 font-medium uppercase tracking-wider mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($headerAction))
                <div>{{ $headerAction }}</div>
            @endif
        </div>
    @endif

    <div @class(['p-6' => $padding])>
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-6 py-3 bg-zinc-50 border-t border-zinc-100">
            {{ $footer }}
        </div>
    @endif
</div>
