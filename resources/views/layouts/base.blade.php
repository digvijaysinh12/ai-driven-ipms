<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AI-IPMS') }} — @yield('title', 'Dashboard')</title>

    {{-- Inter Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-zinc-50 text-zinc-900">

<div class="flex min-h-screen">
    @yield('sidebar')

    <main class="flex-1 flex flex-col min-w-0">
        @yield('topbar')

        <div class="px-6 py-10 lg:px-12 max-w-7xl w-full mx-auto">
            <x-ui.flash />
            @yield('content')
        </div>
    </main>
</div>

</body>
</html>