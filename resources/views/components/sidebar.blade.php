@php $role = auth()->user()->role->name ?? ''; @endphp

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-name">AI Internship</div>
        <div class="sidebar-brand-sub">{{ ucfirst($role) }} Panel</div>
    </div>

    <nav class="sidebar-nav">

        @if($role === 'hr')
            <div class="nav-section-label">Overview</div>
            <a href="{{ route('hr.dashboard') }}"
               class="nav-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}">
                <span class="nav-dot"></span> Dashboard
            </a>

            <div class="nav-section-label">Management</div>
            <a href="{{ route('hr.users') }}"
               class="nav-link {{ request()->routeIs('hr.users') ? 'active' : '' }}">
                <span class="nav-dot"></span> Approvals
            </a>
            <a href="{{ route('hr.mentor.assignments') }}"
               class="nav-link {{ request()->routeIs('hr.mentor.assignments') ? 'active' : '' }}">
                <span class="nav-dot"></span> Assign Mentors
            </a>
            <a href="{{ route('hr.intern.mentor.list') }}"
               class="nav-link {{ request()->routeIs('hr.intern.mentor.list') ? 'active' : '' }}">
                <span class="nav-dot"></span> Intern–Mentor Map
            </a>
            <a href="{{ route('hr.intern.progress') }}"
               class="nav-link {{ request()->routeIs('hr.intern.progress*') ? 'active' : '' }}">
                <span class="nav-dot"></span> Intern Progress
            </a>

        @elseif($role === 'mentor')
            <div class="nav-section-label">Overview</div>
            <a href="{{ route('mentor.dashboard') }}"
               class="nav-link {{ request()->routeIs('mentor.dashboard') ? 'active' : '' }}">
                <span class="nav-dot"></span> Dashboard
            </a>

            <div class="nav-section-label">Manage</div>
            <a href="{{ route('mentor.interns') }}"
               class="nav-link {{ request()->routeIs('mentor.interns*') ? 'active' : '' }}">
                <span class="nav-dot"></span> My Interns
            </a>
            <a href="{{ route('mentor.topics.index') }}"
               class="nav-link {{ request()->routeIs('mentor.topics.*') ? 'active' : '' }}">
                <span class="nav-dot"></span> Topics
            </a>
            <a href="{{ route('mentor.topics.assign') }}"
               class="nav-link {{ request()->routeIs('mentor.topics.assign*') ? 'active' : '' }}">
                <span class="nav-dot"></span> Assign Topic
            </a>

            <div class="nav-section-label">Review</div>
            <a href="{{ route('mentor.assignments') }}"
               class="nav-link {{ request()->routeIs('mentor.assignments') ? 'active' : '' }}">
                <span class="nav-dot"></span> Assignments
            </a>
            <a href="{{ route('mentor.submissions.index') }}"
               class="nav-link {{ request()->routeIs('mentor.submissions.*') ? 'active' : '' }}">
                <span class="nav-dot"></span> Submissions
            </a>

        @elseif($role === 'intern')
            <div class="nav-section-label">Overview</div>
            <a href="{{ route('intern.dashboard') }}"
               class="nav-link {{ request()->routeIs('intern.dashboard') ? 'active' : '' }}">
                <span class="nav-dot"></span> Dashboard
            </a>

            <div class="nav-section-label">Work</div>
            <a href="{{ route('intern.topic') }}"
               class="nav-link {{ request()->routeIs('intern.topic*') ? 'active' : '' }}">
                <span class="nav-dot"></span> My Topic
            </a>
            <a href="{{ route('intern.submissions') }}"
               class="nav-link {{ request()->routeIs('intern.submissions') ? 'active' : '' }}">
                <span class="nav-dot"></span> Submissions
            </a>
            <a href="{{ route('intern.attendance') }}"
               class="nav-link {{ request()->routeIs('intern.attendance') ? 'active' : '' }}">
                <span class="nav-dot"></span> Attendance
            </a>
            <a href="{{ route('intern.performance') }}"
               class="nav-link {{ request()->routeIs('intern.performance') ? 'active' : '' }}">
                <span class="nav-dot"></span> Performance
            </a>
        @endif

    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user-label">Signed in as</div>
        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
        <a href="{{ route('profile.edit') }}" class="sidebar-profile-link">Profile</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn">Sign out</button>
        </form>
    </div>
</aside>