@props([
    'label' => '',
    'value' => '',
    'trend' => null,
    'trendUp' => true,
    'icon' => null,
])

<div class="p-6 bg-white border border-zinc-200 rounded-xl shadow-[0_1px_2px_rgba(0,0,0,0.02)] flex flex-col group hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider">{{ $label }}</span>
        @if($icon)
            <div class="p-2 bg-zinc-50 rounded-lg text-zinc-400 group-hover:text-zinc-900 transition-colors">
                {{ $icon }}
            </div>
        @endif
    </div>

    <div class="mt-4 flex flex-col">
        <span class="text-3xl font-bold tracking-tight text-zinc-900">{{ $value }}</span>
        @if($trend)
            <div @class([
                'flex items-center gap-1.5 mt-2 text-xs font-semibold',
                'text-emerald-600' => $trendUp,
                'text-red-600' => !$trendUp,
            ])>
                @if($trendUp)
                    @include('components.icons.arrow-up-right', ['size' => 12])
                @else
                    @include('components.icons.arrow-down-right', ['size' => 12])
                @endif
                {{ $trend }}
            </div>
        @endif
    </div>
</div>
