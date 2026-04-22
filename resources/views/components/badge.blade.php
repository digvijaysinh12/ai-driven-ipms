@props(['status'])

@php
$status = strtolower($status);
$classes = 'badge px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider ';
if ($status === 'easy') $classes .= 'bg-emerald-50 text-emerald-600 border-emerald-100';
elseif ($status === 'medium') $classes .= 'bg-amber-50 text-amber-600 border-amber-100';
elseif ($status === 'hard') $classes .= 'bg-rose-50 text-rose-600 border-rose-100';
elseif ($status === 'draft') $classes .= 'bg-slate-100 text-slate-500 border-slate-200';
elseif ($status === 'published') $classes .= 'bg-indigo-50 text-indigo-600 border-indigo-100';
elseif (strpos($status, 'ai') !== false) $classes .= 'bg-purple-50 text-purple-600 border-purple-100';
else $classes .= 'bg-slate-100 text-slate-500 border-slate-200';
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot->isEmpty() ? ucfirst($status) : $slot }}
</span>
