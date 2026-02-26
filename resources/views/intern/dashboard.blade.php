@extends('layouts.app')

@section('body')
<div class="p-6">
    <h1 class="text-2xl font-bold">Intern Dashboard</h1>
<div class="max-w-xl bg-white shadow-lg rounded-xl p-6 mt-6 border border-gray-200">

    <h2 class="text-xl font-semibold text-gray-800 mb-4">
        Mentor Information
    </h2>

    <div class="space-y-2 text-gray-600">

        <p>
            <span class="font-medium text-gray-700">Name:</span>
            {{ $assignment->mentor->name }}
        </p>

        <p>
            <span class="font-medium text-gray-700">Email:</span>
            {{ $assignment->mentor->email }}
        </p>

        <p>
            <span class="font-medium text-gray-700">Technology:</span>
            {{ $assignment->mentor->technology->name ?? 'Not Assigned' }}
        </p>

    </div>

</div>
</div>
@endsection