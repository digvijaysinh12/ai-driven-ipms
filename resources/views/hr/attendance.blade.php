<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Attendance Monitoring</h2>
        <p class="text-sm text-slate-500 font-medium">Track intern activity, login periods, and network access points.</p>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary px-4 py-2 text-xs font-bold uppercase tracking-widest gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to Dashboard
        </a>
    </x-slot>

    <!-- Filters -->
    <x-card title="Filter Attendance" icon="filter">
        <form action="{{ route('admin.attendance') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <div class="space-y-2">
                <label class="block text-[10px] font-bold uppercase text-slate-400 tracking-widest">Filter by Intern</label>
                <div class="relative group">
                    <i data-lucide="user" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                    <select name="user_id" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-indigo-600/10 focus:border-indigo-600 transition-all appearance-none cursor-pointer">
                        <option value="">All Interns</option>
                        @foreach($interns as $intern)
                            <option value="{{ $intern->id }}" {{ request('user_id') == $intern->id ? 'selected' : '' }}>{{ $intern->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-bold uppercase text-slate-400 tracking-widest">Select Date</label>
                <div class="relative group">
                    <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-indigo-600/10 focus:border-indigo-600 transition-all">
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary px-6 py-2.5 text-xs font-bold uppercase tracking-widest bg-slate-900 border-slate-900 shadow-xl shadow-slate-900/10 active:scale-95 transition-all flex-1">
                    Filter Logs
                </button>
                <a href="{{ route('admin.attendance') }}" class="btn btn-secondary px-6 py-2.5 text-xs font-bold uppercase tracking-widest flex-1 text-center">
                    Reset
                </a>
            </div>
        </form>
    </x-card>

    <!-- Attendance Table -->
    <x-card title="Daily Attendance Logs" subtitle="Showing records for selected filters" icon="clipboard-list" :padding="false" class="mt-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 font-bold text-slate-400 uppercase tracking-widest text-[10px]">Intern</th>
                        <th class="px-6 py-4 font-bold text-slate-400 uppercase tracking-widest text-[10px]">Date</th>
                        <th class="px-6 py-4 font-bold text-slate-400 uppercase tracking-widest text-[10px]">Portal In</th>
                        <th class="px-6 py-4 font-bold text-slate-400 uppercase tracking-widest text-[10px]">Portal Out</th>
                        <th class="px-6 py-4 font-bold text-slate-400 uppercase tracking-widest text-[10px]">Duration</th>
                        <th class="px-6 py-4 font-bold text-slate-400 uppercase tracking-widest text-[10px]">IP Access</th>
                        <th class="px-6 py-4 font-bold text-slate-400 uppercase tracking-widest text-[10px] text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs uppercase group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                        {{ substr($attendance->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $attendance->user->name }}</div>
                                        <div class="text-[10px] text-slate-500 font-medium">{{ $attendance->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-600 text-xs">
                                {{ $attendance->date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-500">
                                {{ $attendance->login_time->format('h:i A') }}
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-500">
                                {{ $attendance->logout_time ? $attendance->logout_time->format('h:i A') : '--:--' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($attendance->total_seconds)
                                    <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-100 text-slate-900 border border-slate-200">
                                        <i data-lucide="clock" class="w-3 h-3 text-slate-400"></i>
                                        <span class="text-[10px] font-bold tabular-nums">
                                            {{ floor($attendance->total_seconds / 3600) }}h {{ floor(($attendance->total_seconds % 3600) / 60) }}m
                                        </span>
                                    </div>
                                @else
                                    <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider animate-pulse">Active Session</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="badge {{ $attendance->ip_address === env('OFFICE_IP') ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100' }} font-mono text-[10px] px-2 py-1">
                                    {{ $attendance->ip_address }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($attendance->logout_time)
                                    <span class="badge bg-slate-50 text-slate-400 border-slate-100 px-2 py-1 uppercase text-[10px] font-bold">Closed</span>
                                @else
                                    <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider">Live</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-32 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-200 border border-slate-100">
                                        <i data-lucide="database-zap" class="w-8 h-8"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-slate-900 font-bold">No records found</p>
                                        <p class="text-slate-400 font-medium text-xs">Try adjusting your filters to find data.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <x-slot name="footer">
                <div class="px-2">
                    {{ $attendances->links() }}
                </div>
            </x-slot>
        @endif
    </x-card>
</x-app-layout>
