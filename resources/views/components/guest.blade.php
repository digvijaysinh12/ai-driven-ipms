<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="font-sans text-slate-900 antialiased bg-slate-50">

    <div class="min-h-screen flex flex-col justify-center items-center px-4">

        <!-- Logo -->
        <a href="/" class="mb-8 flex items-center gap-2">
            <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center shadow">
                <i data-lucide="brain-circuit" class="text-white w-6 h-6"></i>
            </div>
            <span class="text-2xl font-bold text-slate-900">
                AI-IPMS
            </span>
        </a>

        <!-- Card -->
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg border">
            {{ $slot }}
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>

</body>
</html>