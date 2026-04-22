<x-app-layout>
    <!-- Header -->
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900">HR Command Center</h2>
        <p class="text-sm text-slate-500">Monitor interns, tasks, and attendance</p>
    </x-slot>

    <!-- Actions -->
    <x-slot name="actions">
        <a href="{{ route('admin.users') }}"
           class="px-4 py-2 bg-slate-900 text-white text-xs rounded-lg shadow">
            Review Applications
        </a>
    </x-slot>

    <!-- ================= STATS ================= -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <!-- Interns -->
        <div class="p-6 bg-white rounded-xl shadow">
            <p class="text-xs text-gray-500">Total Interns</p>
            <h3 class="text-2xl font-bold">{{ $totalInterns ?? 0 }}</h3>
        </div>

        <!-- Mentors -->
        <div class="p-6 bg-white rounded-xl shadow">
            <p class="text-xs text-gray-500">Mentors</p>
            <h3 class="text-2xl font-bold">{{ $totalMentors ?? 0 }}</h3>
        </div>

        <!-- Attendance -->
        <div class="p-6 bg-white rounded-xl shadow">
            <p class="text-xs text-gray-500">Today Attendance</p>
            <h3 class="text-2xl font-bold">{{ $todayAttendance ?? 0 }}</h3>
        </div>

        <!-- Pending -->
        <div class="p-6 bg-white rounded-xl shadow">
            <p class="text-xs text-gray-500">Pending Users</p>
            <h3 class="text-2xl font-bold">{{ $pendingUsers ?? 0 }}</h3>
        </div>
    </div>

    <!-- ================= TASK STATS ================= -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <div class="p-6 bg-white rounded-xl shadow">
            <p class="text-xs text-gray-500">Total Tasks</p>
            <h3 class="text-xl font-bold">{{ $totalTasks ?? 0 }}</h3>
        </div>

        <div class="p-6 bg-white rounded-xl shadow">
            <p class="text-xs text-gray-500">Submissions</p>
            <h3 class="text-xl font-bold">{{ $totalSubmissions ?? 0 }}</h3>
        </div>

        <div class="p-6 bg-white rounded-xl shadow">
            <p class="text-xs text-gray-500">Reviewed</p>
            <h3 class="text-xl font-bold">{{ $reviewedCount ?? 0 }}</h3>
        </div>

        <div class="p-6 bg-white rounded-xl shadow">
            <p class="text-xs text-gray-500">Pending Review</p>
            <h3 class="text-xl font-bold">{{ $pendingReviewCount ?? 0 }}</h3>
        </div>

    </div>

    <!-- ================= MAIN SECTION ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- ===== Recent Attendance ===== -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow">

            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="font-semibold text-slate-800">Recent Logins</h3>
                <a href="{{ route('admin.attendance') }}"
                   class="text-xs text-indigo-600 font-semibold">
                    View All
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                        <tr>
                            <th class="px-4 py-3 text-left">User</th>
                            <th class="px-4 py-3">Time</th>
                            <th class="px-4 py-3">IP</th>
                            <th class="px-4 py-3 text-right">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($recentLogins as $login)
                            <tr>
                                <td class="px-4 py-3">
                                    <div>
                                        <div class="font-semibold">
                                            {{ optional($login->user)->name ?? 'Unknown' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ optional($login->user)->email ?? 'N/A' }}
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-xs">
                                    {{ $login->login_time ? \Carbon\Carbon::parse($login->login_time)->format('h:i A') : '-' }}
                                </td>

                                <td class="px-4 py-3 text-xs text-gray-400">
                                    {{ $login->ip_address ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <span class="text-green-600 text-xs font-semibold">
                                        Active
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-gray-400">
                                    No logins today
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== Sidebar ===== -->
        <div class="space-y-6">

            <!-- Platform Health -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-sm font-semibold mb-4">Platform Stats</h3>

                <div class="space-y-3 text-xs">

                    <div class="flex justify-between">
                        <span>Total Tasks</span>
                        <span>{{ $totalTasks ?? 0 }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Submissions</span>
                        <span>{{ $totalSubmissions ?? 0 }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Reviewed</span>
                        <span>{{ $reviewedCount ?? 0 }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Pending Review</span>
                        <span>{{ $pendingReviewCount ?? 0 }}</span>
                    </div>

                </div>
            </div>

            <!-- AI Status -->
            <div class="bg-slate-900 text-white rounded-xl p-6 shadow">
                <h3 class="text-xs uppercase text-indigo-400 font-bold mb-2">
                    AI Status
                </h3>

                <p class="text-xs text-gray-400 mb-4">
                    AI evaluation system is active and processing submissions.
                </p>

                <span class="text-green-400 text-xs font-bold">
                    ● ACTIVE
                </span>
            </div>

        </div>
    </div>

</x-app-layout>
