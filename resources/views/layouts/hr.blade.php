@extends('layouts.app')

@section('body')

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md p-6 flex flex-col justify-between">

        <div>
            <h2 class="text-xl font-bold mb-6">HR Panel</h2>

            <nav class="space-y-2">

                <a href="{{ route('hr.dashboard') }}"
                   class="block px-3 py-2 rounded-lg
                   {{ request()->routeIs('hr.dashboard') ? 'bg-indigo-100 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }}">
                    Dashboard
                </a>

                <a href="{{ route('hr.users') }}"
                   class="block px-3 py-2 rounded-lg
                   {{ request()->routeIs('hr.users') ? 'bg-indigo-100 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }}">
                    Approvals
                </a>

                <a href="{{ route('hr.mentor.assignments') }}"
                   class="block px-3 py-2 rounded-lg
                   {{ request()->routeIs('hr.mentor.assignments') ? 'bg-indigo-100 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600' }}">
                    Mentor Assignment
                </a>

            </nav>
        </div>

        <div class="border-t pt-6">
            <p class="text-sm text-gray-600">Logged in as</p>
            <p class="font-semibold text-gray-800">{{ auth()->user()->name }}</p>

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button class="text-red-500 text-sm hover:text-red-600">
                    Logout
                </button>
            </form>
        </div>

    </aside>

    <!-- Content -->
    <main class="flex-1 p-10">
        @yield('content')
    </main>

</div>

@endsection