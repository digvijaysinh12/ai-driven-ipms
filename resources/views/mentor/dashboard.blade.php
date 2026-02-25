@extends('layouts.mentor')

@section('content')

<x-card title="Mentor Dashboard" subtitle="Manage your assigned interns">

    <p class="text-gray-600">
        Welcome, {{ auth()->user()->name }} 👋
    </p>

</x-card>

@endsection