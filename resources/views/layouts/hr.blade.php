@extends('layouts.base')

<<<<<<< HEAD
@section('sidebar')
    <x-layout.sidebar role="hr" />
@endsection
=======
@section('body')

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

    *, *::before, *::after { box-sizing: border-box; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: #f5f5f4;
        color: #1a1a1a;
    }

    .layout {
        display: flex;
        min-height: 100vh;
    }

    /* ── Sidebar ── */
    .sidebar {
        width: 220px;
        min-width: 220px;
        background: #fff;
        border-right: 1px solid #e5e5e5;
        display: flex;
        flex-direction: column;
        position: sticky;
        top: 0;
        height: 100vh;
    }

    .sidebar-brand {
        padding: 24px 20px 20px;
        border-bottom: 1px solid #e5e5e5;
    }

    .sidebar-brand-name {
        font-size: 13px;
        font-weight: 500;
        color: #1a1a1a;
        letter-spacing: -0.01em;
    }

    .sidebar-brand-sub {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        color: #aaa;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-top: 2px;
    }

    .sidebar-nav {
        flex: 1;
        padding: 16px 12px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .nav-section-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #bbb;
        padding: 10px 8px 6px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 8px 10px;
        border-radius: 2px;
        font-size: 13px;
        color: #555;
        text-decoration: none;
        transition: background 0.12s, color 0.12s;
        gap: 8px;
    }

    .nav-link:hover {
        background: #f5f5f4;
        color: #1a1a1a;
    }

    .nav-link.active {
        background: #f0f0f0;
        color: #1a1a1a;
        font-weight: 500;
    }

    .nav-link .nav-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #ccc;
        flex-shrink: 0;
    }

    .nav-link.active .nav-dot { background: #1a1a1a; }

    .sidebar-footer {
        border-top: 1px solid #e5e5e5;
        padding: 16px 20px;
    }

    .sidebar-user-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        color: #aaa;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .sidebar-user-name {
        font-size: 13px;
        font-weight: 500;
        color: #1a1a1a;
    }

    .logout-btn {
        background: none;
        border: none;
        padding: 0;
        font-size: 12px;
        color: #aaa;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        margin-top: 10px;
        text-decoration: underline;
        text-underline-offset: 2px;
        transition: color 0.12s;
    }

    .logout-btn:hover { color: #1a1a1a; }

    /* ── Main ── */
    .main-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .topbar {
        background: #fff;
        border-bottom: 1px solid #e5e5e5;
        padding: 0 32px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .topbar-title {
        font-size: 14px;
        font-weight: 500;
        color: #1a1a1a;
        letter-spacing: -0.01em;
    }

    .topbar-meta {
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        color: #aaa;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .page-content {
        flex: 1;
        padding: 32px;
    }

    /* ── Flash messages ── */
    .flash {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 2px;
        font-size: 13px;
        margin-bottom: 20px;
        border: 1px solid;
    }

    .flash-success { background: #f0faf0; border-color: #c6e6c6; color: #2d6a2d; }
    .flash-error   { background: #fdf0f0; border-color: #e6c6c6; color: #6a2d2d; }
</style>

<div class="layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-name">AI Internship</div>
            <div class="sidebar-brand-sub">HR Panel</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Overview</div>

            <a href="{{ route('hr.dashboard') }}" class="nav-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}">
                <span class="nav-dot"></span> Dashboard
            </a>

            <div class="nav-section-label" style="margin-top: 8px;">Management</div>

            <a href="{{ route('hr.users') }}" class="nav-link {{ request()->routeIs('hr.users') ? 'active' : '' }}">
                <span class="nav-dot"></span> User Approvals
            </a>

            <a href="{{ route('hr.mentor.assignments') }}" class="nav-link {{ request()->routeIs('hr.mentor.assignments') ? 'active' : '' }}">
                <span class="nav-dot"></span> Mentor Assignment
            </a>

            <a href="{{ route('hr.intern.mentor.list') }}" class="nav-link {{ request()->routeIs('hr.intern.mentor.list') ? 'active' : '' }}">
                <span class="nav-dot"></span> Intern–Mentor Map
            </a>

            <a href="{{ route('hr.intern.progress') }}" class="nav-link {{ request()->routeIs('hr.intern.progress') ? 'active' : '' }}">
                <span class="nav-dot"></span> Intern Progress
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user-label">Signed in as</div>
            <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout-btn">Sign out</button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-col">
        <header class="topbar">
            <span class="topbar-title">@yield('title', 'Dashboard')</span>
            <span class="topbar-meta">HR Panel</span>
        </header>

        <main class="page-content">
            @if(session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flash flash-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

</div>
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26

@section('topbar')
    <x-layout.topbar title="HR Panel" />
@endsection