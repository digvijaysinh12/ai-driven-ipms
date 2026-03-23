<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | AI Internship Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>

<div class="layout">
    {{-- Single sidebar component — switches content by role automatically --}}
    @include('components.sidebar')

    <div class="main-col">
        <header class="topbar">
            <span class="topbar-title">@yield('title', 'Dashboard')</span>
            <span class="topbar-meta">{{ ucfirst(auth()->user()->role->name ?? '') }} Panel</span>
        </header>

        <main class="page-content">
            @include('components.alert')
            @yield('content')
        </main>
    </div>
</div>

@include('components.toast')
@stack('scripts')
</body>
</html>