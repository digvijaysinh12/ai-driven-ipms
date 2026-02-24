@extends('layouts.hr')

@section('content')

<div>

    <!-- Title -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800">
            Pending User Approvals
        </h2>
        <p class="text-gray-500 mt-1">
            Review and approve newly registered users.
        </p>
    </div>

    <!-- Users List -->
    <div class="bg-white rounded-2xl shadow-md divide-y">

        @forelse($users as $user)

        <div class="p-6 flex items-center justify-between">

            <div>
                <h4 class="text-gray-800 font-semibold">
                    {{ $user->name }}
                </h4>

                <p class="text-sm text-gray-500">
                    {{ $user->email }}
                </p>

                <span class="inline-block mt-2 text-xs bg-indigo-100 text-indigo-600 px-3 py-1 rounded-full">
                    {{ ucfirst($user->role->name) }}
                </span>
            </div>

            <div class="flex gap-3">

                <form method="POST" action="{{ route('hr.users.approve', $user->id) }}">
                    @csrf
                    @method('PATCH')
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                        Approve
                    </button>
                </form>

                <form method="POST" action="{{ route('hr.users.reject', $user->id) }}">
                    @csrf
                    @method('PATCH')
                    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                        Reject
                    </button>
                </form>

            </div>

        </div>

        @empty
        <div class="p-10 text-center text-gray-500">
            No pending users found.
        </div>
        @endforelse

    </div>

</div>

@endsection


@push('scripts')

<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});
</script>

@if(session('success'))
<script>
    Toast.fire({
        icon: 'success',
        title: "{{ session('success') }}"
    });
</script>
@endif

@if(session('error'))
<script>
    Toast.fire({
        icon: 'error',
        title: "{{ session('error') }}"
    });
</script>
@endif

@if($errors->any())
<script>
    Toast.fire({
        icon: 'error',
        title: "{{ $errors->first() }}"
    });
</script>
@endif
@endpush