<x-app-layout>

    <!-- Header -->
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900">Mentor Assignment</h2>
        <p class="text-sm text-slate-500">Assign mentors to approved interns</p>
    </x-slot>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- ================= INTERN LIST ================= -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow">

            <div class="p-4 border-b">
                <h3 class="font-semibold text-slate-800">
                    Unassigned Interns ({{ $interns->count() ?? 0 }})
                </h3>
            </div>

            <div class="divide-y">

                @forelse($interns as $intern)
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                        <!-- Intern Info -->
                        <div>
                            <div class="font-bold text-slate-900">
                                {{ $intern->name ?? 'Unknown' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $intern->email ?? 'N/A' }}
                            </div>

                            @if(isset($intern->metadata['technology']))
                                <span class="inline-block mt-1 text-[10px] bg-gray-100 px-2 py-1 rounded">
                                    {{ $intern->metadata['technology'] }}
                                </span>
                            @endif
                        </div>

                        <!-- Assign Form -->
                        <form method="POST"
                              action="{{ route('admin.assigned.mentor') }}"
                              class="flex gap-2 w-full sm:w-auto">

                            @csrf

                            <input type="hidden" name="intern_id" value="{{ $intern->id }}">

                            <!-- Mentor Select -->
                            <select name="mentor_id"
                                    required
                                    class="border rounded px-2 py-1 text-sm w-full sm:w-48">
                                <option value="">Select Mentor</option>
                                @forelse($mentors as $mentor)
                                    <option value="{{ $mentor->id }}">
                                        {{ $mentor->name ?? 'Unknown' }}
                                        ({{ $mentor->assignedInternsCount ?? 0 }})
                                    </option>
                                @empty
                                    <option disabled>No mentors available</option>
                                @endforelse
                            </select>

                            <!-- Button -->
                            <button type="submit"
                                    class="bg-slate-900 text-white px-4 py-1 text-xs rounded">
                                Assign
                            </button>

                        </form>

                    </div>
                @empty
                    <div class="p-10 text-center text-gray-400">
                        No interns available for assignment
                    </div>
                @endforelse

            </div>

        </div>

        <!-- ================= MENTOR OVERVIEW ================= -->
        <div class="bg-white rounded-xl shadow p-4">

            <h3 class="font-semibold text-slate-800 mb-4">Mentor Load</h3>

            <div class="space-y-3">

                @forelse($mentors as $mentor)
                    <div class="flex justify-between items-center border-b pb-2">

                        <div>
                            <div class="font-semibold text-sm">
                                {{ $mentor->name ?? 'Unknown' }}
                            </div>
                            <div class="text-[10px] text-gray-400">
                                Mentor
                            </div>
                        </div>

                        <div class="text-sm font-bold text-slate-900">
                            {{ $mentor->assignedInternsCount ?? 0 }}
                        </div>

                    </div>
                @empty
                    <div class="text-gray-400 text-sm">
                        No mentors found
                    </div>
                @endforelse

            </div>

        </div>

    </div>

</x-app-layout>
