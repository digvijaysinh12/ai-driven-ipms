<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<div class="layout">
    @yield('sidebar')

    <div class="main-col">
        @yield('topbar')

        <div class="page-content">
            <x-ui.flash />
            @yield('content')
        </div>
    </div>
</div>

</body>
</html>