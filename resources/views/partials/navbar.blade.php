<header class="topbar">
    <div class="topbar-copy">
        <h2 class="topbar-title">@yield('title', 'Welcome Back')</h2>
        <p class="topbar-subtitle">Intern Management System v1.0</p>
    </div>

    <div class="topbar-user">
        <div class="topbar-user-copy">
            <p class="topbar-user-name">{{ Auth::user()->name }}</p>
            <p class="topbar-user-role">{{ ucfirst(Auth::user()->role->name ?? 'User') }}</p>
        </div>
        <div class="topbar-user-avatar">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
    </div>
</header>
