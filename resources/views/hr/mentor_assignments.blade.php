@extends('layouts.hr')

@section('content')

<x-card title="Mentor Assignment">

@forelse($interns as $intern)

<div class="flex justify-between items-center border-b py-4">

    <div>
        <p class="font-semibold">{{ $intern->name }}</p>
        <p class="text-sm text-gray-500">{{ $intern->email }}</p>
    </div>

    <form method="POST" action="{{ route('hr.assigned.mentor') }}" class="flex gap-3">
        @csrf
        <input type="hidden" name="intern_id" value="{{ $intern->id }}">

        <select name="mentor_id" class="border rounded-lg px-3 py-2 text-sm" required>
            <option value="">Select Mentor</option>
            @foreach($mentors as $mentor)
                <option value="{{ $mentor->id }}">
                    {{ $mentor->name }}
                </option>
            @endforeach
        </select>

        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">
            Assign
        </button>
    </form>

</div>

@empty
    <p class="text-gray-500">No interns available.</p>
@endforelse

</x-card>

@endsection