<x-app-layout>

    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900">Assignment Map</h2>
        <p class="text-sm text-slate-500">Intern–Mentor relationships</p>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.mentor.assignments') }}"
           class="px-4 py-2 bg-slate-900 text-white text-xs rounded">
            Assign Mentors
        </a>
    </x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-6 mb-6">

        <div class="bg-white p-4 rounded shadow">
            <p class="text-xs text-gray-500">Total Assignments</p>
            <h3 class="text-xl font-bold">{{ $mappings->total() }}</h3>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <p class="text-xs text-gray-500">Avg Interns per Mentor</p>
            <h3 class="text-xl font-bold">
                {{
                    $mappings->count() > 0
                    ? round($mappings->count() / max($mappings->unique('mentor_id')->count(), 1), 1)
                    : 0
                }}
            </h3>
        </div>

    </div>

    <!-- Table -->
    <div class="bg-white rounded shadow overflow-x-auto">

        <table class="w-full text-sm">

            <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                <tr>
                    <th class="px-4 py-3 text-left">Intern</th>
                    <th class="px-4 py-3">Mentor</th>
                    <th class="px-4 py-3 text-center">Duration</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y">

                @forelse($mappings as $map)
                    <tr>

                        <!-- Intern -->
                        <td class="px-4 py-3">
                            <div class="font-semibold">
                                {{ $map->intern->name ?? 'Unknown' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $map->intern->email ?? 'N/A' }}
                            </div>
                        </td>

                        <!-- Mentor -->
                        <td class="px-4 py-3">
                            {{ $map->mentor->name ?? 'Unknown' }}
                        </td>

                        <!-- Duration -->
                        <td class="px-4 py-3 text-center text-xs">
                            {{ $map->created_at ? $map->created_at->diffInDays() : 0 }} days
                        </td>

                        <!-- Action -->
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('hr.intern.progress.show', $map->intern_id) }}"
                               class="text-indigo-600 text-xs">
                                View
                            </a>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-gray-400">
                            No assignments found
                        </td>
                    </tr>
                @endforelse

            </tbody>

        </table>

        <!-- Pagination -->
        <div class="p-4">
            {{ $mappings->links() }}
        </div>

    </div>

</x-app-layout>
