@props([
    'role' => 'intern',
])

@php
    $nav = match($role) {
        'hr' => [
            ['label' => 'Dashboard', 'route' => 'hr.dashboard', 'icon' => 'home'],
            ['label' => 'User Approvals', 'route' => 'hr.users', 'icon' => 'user-plus'],
            ['label' => 'Assignment Map', 'route' => 'hr.intern.mentor.list', 'icon' => 'map'],
            ['label' => 'Progress tracking', 'route' => 'hr.intern.progress', 'icon' => 'chart-bar'],
        ],
        'mentor' => [
            ['label' => 'Dashboard', 'route' => 'user.mentor.dashboard', 'icon' => 'home'],
            ['label' => 'My Interns', 'route' => 'user.mentor.interns', 'icon' => 'users'],
            ['label' => 'Task Management', 'route' => 'user.mentor.tasks.index', 'icon' => 'clipboard-list'],
            ['label' => 'Review Queue', 'route' => 'user.mentor.submissions.index', 'icon' => 'check-circle'],
        ],
        default => [
            ['label' => 'Overview', 'route' => 'user.intern.dashboard', 'icon' => 'home'],
            ['label' => 'Coursework', 'route' => 'user.intern.tasks', 'icon' => 'book-open'],
            ['label' => 'Performance', 'route' => 'user.intern.performance', 'icon' => 'academic-cap'],
        ],
    };
@endphp

<aside class="w-64 bg-white border-r border-zinc-200 flex flex-col fixed inset-y-0 left-0 z-50 transition-transform lg:translate-x-0 -translate-x-full" id="sidebar">
    <div class="p-6 flex items-center gap-3">
        <div class="h-8 w-8 bg-zinc-900 rounded-lg flex items-center justify-center text-white font-bold">A</div>
        <span class="font-semibold text-zinc-900 tracking-tight">AI-IPMS</span>
    </div>

    <nav class="flex-1 px-4 space-y-1 overflow-y-auto pt-4">
        @foreach($nav as $item)
            @php $isActive = request()->routeIs($item['route'] . '*'); @endphp
            <a href="{{ route($item['route']) }}" 
               @class([
                   'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors group',
                   'bg-zinc-100 text-zinc-900' => $isActive,
                   'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' => !$isActive,
               ])>
                <div @class(['text-zinc-400 group-hover:text-zinc-900', 'text-zinc-900' => $isActive])>
                    @include('components.icons.' . $item['icon'], ['size' => 18])
                </div>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="p-4 border-t border-zinc-100">
        <div class="flex items-center gap-3 px-3 py-3 rounded-lg bg-zinc-50 border border-zinc-200/50">
            <div class="h-9 w-9 rounded-full bg-zinc-900 flex items-center justify-center text-white text-xs font-bold uppercase overflow-hidden">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-zinc-900 truncate">{{ auth()->user()->name ?? 'User' }}</div>
                <div class="text-[11px] text-zinc-500 font-medium uppercase tracking-wider">{{ $role }}</div>
            </div>
        </div>
    </div>
</aside>
