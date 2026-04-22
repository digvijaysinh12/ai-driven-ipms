@php
    $role = auth()->user()->role->name ?? '';
    $userName = auth()->user()->name;
    $userEmail = auth()->user()->email;
@endphp

<aside class="w-64 bg-white border-r border-slate-200 flex flex-col h-screen sticky top-0 z-40">
    <!-- Brand -->
    <div class="px-6 py-8 flex items-center gap-3">
        <div class="w-10 h-10 bg-slate-950 rounded-xl flex items-center justify-center p-2 shadow-lg shadow-slate-950/20">
            <i data-lucide="brain-circuit" class="text-white w-6 h-6"></i>
        </div>
        <div>
            <h1 class="text-lg font-bold tracking-tight text-slate-900 leading-none">AI-IPMS</h1>
            <p class="text-[10px] font-medium text-indigo-600 uppercase tracking-widest mt-1">{{ $role }} panel</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 space-y-8 overflow-y-auto pt-4 custom-scrollbar">
        @if($role === 'hr')
            <div class="space-y-1">
                <p class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Overview</p>
                <x-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('hr.dashboard')" icon="layout-dashboard">
                    Dashboard
                </x-nav-link>
            </div>

            <div class="space-y-1">
                <p class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Management</p>
                <x-nav-link href="{{ route('admin.users') }}" :active="request()->routeIs('hr.users')" icon="user-check">
                    Approvals
                </x-nav-link>
                <x-nav-link href="{{ route('admin.mentor.assignments') }}" :active="request()->routeIs('hr.mentor.assignments')" icon="user-plus">
                    Assign Mentors
                </x-nav-link>
                <x-nav-link href="{{ route('admin.intern.mentor.list') }}" :active="request()->routeIs('hr.intern.mentor.list')" icon="users">
                    Intern-Mentor Map
                </x-nav-link>
                <x-nav-link href="{{ route('admin.intern.progress') }}" :active="request()->routeIs('hr.intern.progress*')" icon="bar-chart-3">
                    Intern Progress
                </x-nav-link>
                <x-nav-link href="{{ route('admin.attendance') }}" :active="request()->routeIs('hr.attendance')" icon="calendar-check">
                    Attendance
                </x-nav-link>
            </div>

        @elseif($role === 'mentor')
            <div class="space-y-1">
                <p class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Overview</p>
                <x-nav-link href="{{ route('user.mentor.dashboard') }}" :active="request()->routeIs('user.mentor.dashboard')" icon="layout-dashboard">
                    Dashboard
                </x-nav-link>
            </div>

            <div class="space-y-1">
                <p class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Management</p>
                <x-nav-link href="{{ route('user.mentor.interns') }}" :active="request()->routeIs('user.mentor.interns*')" icon="users-2">
                    My Interns
                </x-nav-link>
                <x-nav-link href="{{ route('user.mentor.tasks.index') }}" :active="request()->routeIs('user.mentor.tasks.*')" icon="file-code">
                    Tasks
                </x-nav-link>
                <x-nav-link href="{{ route('user.mentor.tasks.create') }}" :active="request()->routeIs('user.mentor.tasks.create')" icon="plus-circle">
                    Create Task
                </x-nav-link>
            </div>

            <div class="space-y-1">
                <p class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Evaluation</p>
                <x-nav-link href="{{ route('user.mentor.tasks.index') }}" :active="request()->routeIs('user.mentor.tasks.*')" icon="clipboard-list">
                    Active Tasks
                </x-nav-link>
                <x-nav-link href="{{ route('user.mentor.submissions.index') }}" :active="request()->routeIs('user.mentor.submissions.*')" icon="check-square">
                    Reviews
                </x-nav-link>
            </div>

        @elseif($role === 'intern')
            <div class="space-y-1">
                <p class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Overview</p>
                <x-nav-link href="{{ route('user.intern.dashboard') }}" :active="request()->routeIs('user.intern.dashboard')" icon="layout-dashboard">
                    Dashboard
                </x-nav-link>
            </div>

            <div class="space-y-1">
                <p class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Work</p>
                <x-nav-link href="{{ route('user.intern.tasks') }}" :active="request()->routeIs('user.intern.tasks*')" icon="file-code">
                    My Tasks
                </x-nav-link>
                <x-nav-link href="{{ route('user.intern.submissions') }}" :active="request()->routeIs('user.intern.submissions*')" icon="clipboard-check">
                    Submissions
                </x-nav-link>
            </div>

            <div class="space-y-1">
                <p class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Activity</p>
                <x-nav-link href="{{ route('user.intern.attendance') }}" :active="request()->routeIs('user.intern.attendance')" icon="calendar-check">
                    Attendance
                </x-nav-link>
                <x-nav-link href="{{ route('user.intern.performance') }}" :active="request()->routeIs('user.intern.performance')" icon="trending-up">
                    Performance
                </x-nav-link>
            </div>
        @endif
    </nav>

    <!-- User Profile -->
    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
        <div class="flex items-center gap-3 px-2 py-3 rounded-xl bg-white border border-slate-200 shadow-sm mb-4">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-lg border border-indigo-100">
                {{ substr($userName, 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-900 truncate">{{ $userName }}</p>
                <p class="text-[10px] text-slate-500 truncate">{{ $userEmail }}</p>
            </div>
        </div>
        
        <div class="space-y-1">
            <a href="{{ route('user.profile.edit') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-white hover:shadow-sm rounded-lg transition-all group">
                <i data-lucide="settings" class="w-4 h-4 group-hover:rotate-45 transition-transform"></i>
                Settings
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition-all group">
                    <i data-lucide="log-out" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform"></i>
                    Sign out
                </button>
            </form>
        </div>
    </div>
</aside>
