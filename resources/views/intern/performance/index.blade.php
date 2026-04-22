@extends('layouts.app')
@section('title', 'Performance')

@section('content')
@php
    $completionPercent = $assignmentCount > 0 ? round(($submittedAssignments / $assignmentCount) * 100) : 0;
    $reviewCoveragePercent = $submittedAssignments > 0 ? round(($evaluatedAssignments / $submittedAssignments) * 100) : 0;
    $scorePercent = $averageFinalScore !== null ? min(100, round(($averageFinalScore / 30) * 100)) : 0;
    $latestGrade = $latestEvaluatedAssignment->grade ?? 'N/A';
@endphp

<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">Intern analytics</div>
        <h1 class="page-shell-title">Performance</h1>
        <p class="page-shell-subtitle">
            Track how much work you have completed, how much has been reviewed, and how your latest grading is trending.
        </p>
    </div>
    <div class="page-shell-actions">
        <x-ui.button :href="route('user.intern.tasks')" variant="secondary">Back to Tasks</x-ui.button>
    </div>
</div>

<div class="summary-strip">
    <div class="summary-card">
        <div class="summary-label">Assignments</div>
        <div class="summary-value">{{ $assignmentCount }}</div>
        <div class="summary-note">{{ $submittedAssignments }} already submitted.</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Reviewed Answers</div>
        <div class="summary-value">{{ $reviewedAnswers }}</div>
        <div class="summary-note">Answers finalized after mentor review.</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Average Score</div>
        <div class="summary-value">{{ $averageFinalScore ?? 'N/A' }}</div>
        <div class="summary-note">Average final score across all graded submissions.</div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-stack">
        <x-ui.card title="Progress Bars" subtitle="A quick read on submission, evaluation, and score progress.">
            <div class="metric-stack">
                <div class="metric-row">
                    <div class="metric-head">
                        <div class="metric-title">Task Completion</div>
                        <div class="metric-value">{{ $completionPercent }}%</div>
                    </div>
                    <div class="metric-bar">
                        <div class="metric-bar-fill" style="width: {{ $completionPercent }}%"></div>
                    </div>
                </div>

                <div class="metric-row">
                    <div class="metric-head">
                        <div class="metric-title">Evaluation Coverage</div>
                        <div class="metric-value">{{ $reviewCoveragePercent }}%</div>
                    </div>
                    <div class="metric-bar">
                        <div class="metric-bar-fill is-warning" style="width: {{ $reviewCoveragePercent }}%"></div>
                    </div>
                </div>

                <div class="metric-row">
                    <div class="metric-head">
                        <div class="metric-title">Score Health</div>
                        <div class="metric-value">{{ $scorePercent }}%</div>
                    </div>
                    <div class="metric-bar">
                        <div class="metric-bar-fill is-success" style="width: {{ $scorePercent }}%"></div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        @if($topicPerformance->isNotEmpty())
            <x-ui.card title="Topic Breakdown" subtitle="Performance by task or topic assignment.">
                <x-ui.table>
                    <thead>
                        <tr>
                            <th>Topic</th>
                            <th>AI Avg</th>
                            <th>Final Avg</th>
                            <th>Reviewed</th>
                            <th>Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topicPerformance as $topicRow)
                            <tr>
                                <td>
                                    <div class="table-title">{{ $topicRow->topic->title ?? 'Untitled topic' }}</div>
                                    <div class="table-subtitle">{{ optional($topicRow->deadline)->format('d M Y') ?? 'No deadline' }}</div>
                                </td>
                                <td class="cell-mono">{{ $topicRow->ai_score ?? 'N/A' }}</td>
                                <td class="cell-mono">{{ $topicRow->final_score ?? 'N/A' }}</td>
                                <td class="cell-mono">{{ $topicRow->reviewed_answers }}</td>
                                <td><span class="role-pill">{{ $topicRow->grade ?? 'N/A' }}</span></td>
                                <td><x-badge :status="$topicRow->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            </x-ui.card>
        @endif
    </div>

    <div class="dashboard-stack">
        <x-ui.card title="Charts" subtitle="Simple chart-style indicators for your current standing.">
            <div class="donut-chart" style="--value: {{ $completionPercent }};" data-label="{{ $completionPercent }}%"></div>
            <div class="chart-caption">Completion chart showing how much of your assigned work has been submitted.</div>
        </x-ui.card>

        <div class="grade-display-card">
            <div class="grade-display-letter">{{ $latestGrade }}</div>
            <div class="grade-display-copy">
                <div class="grade-display-title">Latest Grade</div>
                <div class="grade-display-subtitle">
                    @if($latestEvaluatedAssignment)
                        {{ $latestEvaluatedAssignment->topic->title ?? 'Latest assignment' }} was your most recently evaluated task.
                    @else
                        Your latest grade will appear here once an assignment is evaluated.
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
