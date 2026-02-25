@extends('layouts.hr')

@section('content')

<x-card title="Pending User Approvals">

    @forelse($users as $user)
        <div class="flex justify-between items-center border-b py-4">

            <div>
                <p class="font-semibold">{{ $user->name }}</p>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
            </div>

            <div class="flex gap-3">
                <form method="POST" action="{{ route('hr.users.approve',$user->id) }}">
                    @csrf @method('PATCH')
                    <button class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                        Approve
                    </button>
                </form>

                <form method="POST" action="{{ route('hr.users.reject',$user->id) }}">
                    @csrf @method('PATCH')
                    <button class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                        Reject
                    </button>
                </form>
            </div>

        </div>
    @empty
        <p class="text-gray-500">No pending users.</p>
    @endforelse

</x-card>

@endsection