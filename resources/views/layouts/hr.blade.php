<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
</head>

<body class="bg-gray-100">

    <!-- Top Navbar -->
    <nav class="bg-white shadow px-8 py-4 flex justify-between items-center">
        <h1 class="text-lg font-semibold text-gray-800">
            AI Internship System
        </h1>

        <div class="flex items-center gap-4">
            <span class="text-gray-600 text-sm">
                Welcome, {{ auth()->user()->name }}
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-red-500 border border-red-500 px-3 py-1 rounded-md text-sm hover:bg-red-50">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="max-w-7xl mx-auto mt-10 px-6">
        @yield('content')
    </main>

</body>
</html>