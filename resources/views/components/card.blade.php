@props(['title' => null, 'subtitle' => null, 'icon' => null, 'padding' => true])

<div {{ $attributes->merge(['class' => 'card bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col']) }}>
    @if($title || $subtitle || $icon)
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-4">
                @if($icon)
                    <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-900 border border-slate-100 flex items-center justify-center">
                        <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
                    </div>
                @endif
                <div>
                    @if($title)
                        <h3 class="text-sm font-bold text-slate-900 leading-none">{{ $title }}</h3>
                    @endif
                    @if($subtitle)
                        <p class="text-[11px] font-medium text-slate-400 mt-1">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            @if(isset($headerActions))
                <div>{{ $headerActions }}</div>
            @endif
        </div>
    @endif

    <div class="{{ $padding ? 'p-6' : '' }} flex-1">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
            {{ $footer }}
        </div>
    @endif
</div>
