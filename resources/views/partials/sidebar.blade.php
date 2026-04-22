<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-name">IPMS</div>
        <div class="sidebar-brand-sub">AI-Powered Intern Management</div>
    </div>

    <nav class="sidebar-nav">
        @php
            $role = auth()->user()->role->name ?? 'member';
        @endphp

        <!-- Common Sections -->
        <div class="nav-section-label">General</div>
        <a href="{{ route($role === 'hr' ? 'admin.dashboard' : ($role === 'mentor' ? 'user.mentor.dashboard' : 'user.intern.dashboard')) }}" class="nav-link {{ request()->routeIs($role . '.dashboard') ? 'active' : '' }}">
            <span class="nav-dot"></span>
            Dashboard
        </a>

        @if($role === 'mentor')
            <div class="nav-section-label">Management</div>
            
            <a href="{{ route('user.mentor.interns') }}" class="nav-link {{ request()->routeIs('user.mentor.interns*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                Interns
            </a>

            <a href="{{ route('user.mentor.tasks.index') }}" class="nav-link {{ request()->routeIs('user.mentor.tasks*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                Tasks
            </a>

        @elseif($role === 'intern')
            <div class="nav-section-label">Learning</div>
            
            <a href="{{ route('user.intern.tasks') }}" class="nav-link {{ request()->routeIs('user.intern.tasks*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                My Tasks
            </a>

            <a href="{{ route('user.intern.submissions') }}" class="nav-link {{ request()->routeIs('user.intern.submissions*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                My Submissions
            </a>

            <a href="{{ route('user.intern.performance') }}" class="nav-link {{ request()->routeIs('user.intern.performance*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                Performance
            </a>

        @elseif($role === 'hr')
            <div class="nav-section-label">Organization</div>
            
            <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('hr.users*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                User Management
            </a>

            <a href="{{ route('admin.mentor.assignments') }}" class="nav-link {{ request()->routeIs('hr.mentor.assignments*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                Mentor Assignment
            </a>

            <a href="{{ route('admin.intern.progress') }}" class="nav-link {{ request()->routeIs('hr.intern.progress*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                Intern Progress
            </a>
        @endif

        <div class="nav-section-label">Account</div>
        <a href="{{ route('user.profile.edit') }}" class="nav-link {{ request()->routeIs('user.profile.edit') ? 'active' : '' }}">
            <span class="nav-dot"></span>
            Profile
        </a>
    </nav>

    <!-- Logout -->
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <span class="nav-dot" style="background:#ff4d4d"></span>
                Logout
            </button>
        </form>
    </div>
</aside>
