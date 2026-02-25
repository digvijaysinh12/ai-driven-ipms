@extends('layouts.app')

@section('body')

<div class="min-h-screen p-10">

    <div class="flex justify-between mb-8">
        <h2 class="text-2xl font-bold">Intern Dashboard</h2>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-red-500 hover:text-red-600 text-sm">
                Logout
            </button>
        </form>
    </div>

    @yield('content')

</div>

@endsection