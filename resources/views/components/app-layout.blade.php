@props(['header' => null, 'actions' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50/50">
    <div class="min-h-screen flex bg-slate-50/50">
        <x-sidebar />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <x-topbar />

            <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 md:p-8">
                <div class="max-w-7xl mx-auto space-y-6">
                    @if ($header)
                        <header class="flex items-center justify-between">
                            <div class="space-y-1">
                                {{ $header }}
                            </div>
                            @if ($actions)
                                <div class="flex items-center gap-3">
                                    {{ $actions }}
                                </div>
                            @endif
                        </header>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <x-toast />
    <script>lucide.createIcons();</script>
</body>
</html>
