@extends('layouts.hr')
@section('title', 'Intern Progress — ' . $intern->name)

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">{{ $intern->name }}</h1>
        <p class="page-subtitle">{{ $intern->email }}</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('admin.intern.mentor.list') }}" class="btn btn-secondary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px;" class="mr-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Back to Map
        </a>
    </div>
</header>

<div class="stats-grid mb-8">
    <x-ui.stat-card 
        label="Assigned Mentor" 
        :value="$mentorAssignment?->mentor?->name ?? 'None'" 
        trend="Primary Support"
        :trendUp="true"
    />
    <x-ui.stat-card 
        label="Topics" 
        :value="$topicAssignments->count()" 
        trend="Total Assignments"
        :trendUp="true"
    />
    <x-ui.stat-card 
        label="Submissions" 
        :value="$totalSubmissions" 
        trend="Evaluated: $reviewedCount"
        :trendUp="true"
    />
</div>

<x-ui.card title="Topic Assignments" subtitle="Historical breakdown of all issued tasks and their results." padding="false">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Topic</th>
                    <th>Status</th>
                    <th>Result</th>
                    <th>Deadline</th>
                    <th class="text-right">Assigned On</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topicAssignments as $asgn)
                    @php
                        $isOverdue = \Carbon\Carbon::parse($asgn->deadline)->isPast() 
                            && !in_array($asgn->status, ['submitted', 'evaluated']);
                    @endphp
                    <tr>
                        <td>
                            <div class="font-medium text-gray-900">{{ $asgn->topic->title ?? 'Untitled Topic' }}</div>
                        </td>
                        <td>
                            <x-ui.badge :type="$asgn->status === 'evaluated' ? 'success' : ($asgn->status === 'submitted' ? 'info' : 'warning')" dot="true">
                                {{ ucfirst(str_replace('_', ' ', $asgn->status)) }}
                            </x-ui.badge>
                        </td>
                        <td>
                            @if($asgn->grade)
                                <div @class([
                                    'inline-flex items-center justify-center w-7 h-7 rounded text-xs font-bold font-mono border',
                                    'bg-success-bg text-success border-emerald-100' => in_array($asgn->grade, ['A', 'B']),
                                    'bg-warning-bg text-warning border-amber-100' => in_array($asgn->grade, ['C', 'D']),
                                    'bg-error-bg text-error border-red-100' => $asgn->grade === 'F',
                                ])>
                                    {{ $asgn->grade }}
                                </div>
                            @else
                                <span class="text-gray-300 font-mono">—</span>
                            @endif
                        </td>
                        <td>
                            <div @class(['text-xs font-mono', 'text-error font-semibold' => $isOverdue, 'text-gray-500' => !$isOverdue])>
                                {{ \Carbon\Carbon::parse($asgn->deadline)->format('d M, Y') }}
                                @if($isOverdue)
                                    <span class="block text-[10px] uppercase tracking-wider mt-0.5">Overdue</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-right">
                            <span class="text-xs text-gray-400 font-mono">
                                {{ \Carbon\Carbon::parse($asgn->assigned_at)->format('d M, Y') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state py-12 text-center text-gray-400">
                                No topics have been assigned to this intern yet.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
