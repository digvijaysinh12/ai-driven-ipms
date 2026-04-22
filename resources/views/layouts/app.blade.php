<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

.animate-fadeIn {
    animation: fadeIn 0.2s ease-in-out;
}
</style>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50/50">
    <div class="min-h-screen flex bg-slate-50/50">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Topbar -->
            <x-topbar />

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 md:p-8">
                <div class="max-w-7xl mx-auto space-y-6">
            @if (isset($header))
                <header class="flex items-center justify-between">
                    <div class="space-y-1">
                        {{ $header }}
                    </div>
                    @if (isset($actions))
                        <div class="flex items-center gap-3">
                            {{ $actions }}
                        </div>
                    @endif
                </header>
            @endif

                    @isset($slot)
                        {{ $slot }}
                    @endisset

                    @hasSection('content')
                        @yield('content')
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    <script>
        lucide.createIcons();
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function () {

    // OPEN MODAL
    document.querySelectorAll('[data-modal-open]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-modal-open');
            document.getElementById(id)?.classList.remove('hidden');
        });
    });

    // CLOSE MODAL
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-modal-close');
            document.getElementById(id)?.classList.add('hidden');
        });
    });

    // ESC KEY CLOSE
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id]').forEach(modal => {
                if (!modal.classList.contains('hidden') && modal.classList.contains('fixed')) {
                    modal.classList.add('hidden');
                }
            });
        }
    });

});
</script>
</body>
</html>
