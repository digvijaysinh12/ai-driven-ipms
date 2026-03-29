@props(['role'])

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-name">AI-IPMS</div>
    </div>

    <nav class="sidebar-nav">

        {{-- INTERN --}}
        @if($role === 'intern')
            <a href="/intern/dashboard" class="nav-link">Dashboard</a>
            <a href="/intern/attendance" class="nav-link">Attendance</a>
            <a href="/intern/exercise" class="nav-link">Exercises</a>
            <a href="/intern/performance" class="nav-link">Performance</a>
        @endif

        {{-- MENTOR --}}
        @if($role === 'mentor')
            <a href="/mentor/dashboard" class="nav-link">Dashboard</a>
            <a href="/mentor/topics" class="nav-link">Topics</a>
            <a href="/mentor/questions" class="nav-link">Questions</a>
            <a href="/mentor/reviews" class="nav-link">Reviews</a>
        @endif

        {{-- HR --}}
        @if($role === 'hr')
            <a href="/hr/dashboard" class="nav-link">Dashboard</a>
            <a href="/hr/users" class="nav-link">User Approval</a>
            <a href="/hr/assignments" class="nav-link">Assignments</a>
            <a href="/hr/reports" class="nav-link">Reports</a>
        @endif

    </nav>
</div>