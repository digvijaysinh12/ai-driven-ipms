<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900">Access Control</h2>
        <p class="text-sm text-slate-500">Approve or reject users</p>
    </x-slot>

    <div class="bg-white rounded-xl shadow">

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                    <tr>
                        <th class="px-6 py-4 text-left">User</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @forelse($pendingUsers as $user)
                        <tr>

                            <!-- User -->
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-bold">
                                        {{ $user->name ?? 'Unknown' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $user->email ?? 'N/A' }}
                                    </div>
                                </div>
                            </td>

                            <!-- Role -->
                            <td class="px-6 py-4">
                                <span class="text-xs bg-gray-100 px-2 py-1 rounded">
                                    {{ optional($user->role)->name ?? 'None' }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 text-center">
                                <span class="text-yellow-600 text-xs font-bold">
                                    Pending
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">

                                    <!-- Approve -->
                                    <form method="POST" action="{{ route('hr.users.approve', $user->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-1 bg-green-600 text-white text-xs rounded">
                                            Approve
                                        </button>
                                    </form>

                                    <!-- Reject -->
                                    <form method="POST" action="{{ route('hr.users.reject', $user->id) }}"
                                          onsubmit="return confirm('Reject this user?')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-1 bg-red-600 text-white text-xs rounded">
                                            Reject
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-10 text-gray-400">
                                No pending users
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4">
            {{ $pendingUsers->links() }}
        </div>

    </div>
</x-app-layout>