@extends('layouts.app')
@section('title', 'My Performance')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">My Performance</div>
        <div class="page-meta">Live metrics from assignments, submissions, and reviews</div>
    </div>
</div>

<div class="stat-mosaic" style="margin-bottom: 28px;">
    <x-stat-card label="Assignments" :value="$assignmentCount" />
    <x-stat-card label="Submitted" :value="$submittedAssignments" />
    <x-stat-card label="Evaluated" :value="$evaluatedAssignments" accent="accent" />
    <x-stat-card label="Reviewed Answers" :value="$reviewedAnswers" />
    <x-stat-card label="Avg Final Score" :value="$averageFinalScore ?? 'N/A'" accent="warn" />
</div>

@if($latestEvaluatedAssignment)
    <div class="section-label">Latest Evaluation</div>
    <div class="final-grade-card" style="margin-bottom: 28px;">
        <div>
            <div class="grade-letter-big">{{ $latestEvaluatedAssignment->grade ?? 'N/A' }}</div>
            <div class="section-label" style="margin-top: 8px;">{{ $latestEvaluatedAssignment->topic->title ?? 'Topic' }}</div>
        </div>
        <div>
            <div class="section-label" style="margin-bottom: 6px;">Assignment Feedback</div>
            @if($latestEvaluatedAssignment->feedback)
                <div style="font-size: 13.5px; color: #444; line-height: 1.8;">
                    {{ $latestEvaluatedAssignment->feedback }}
                </div>
            @else
                <div style="font-size: 13.5px; color: #777; line-height: 1.8;">
                    The assignment has been evaluated, but no feedback text was saved yet.
                </div>
            @endif
        </div>
    </div>
@else
    <div class="table-card" style="padding: 28px; text-align: center; font-family: 'DM Mono', monospace; color: #888; margin-bottom: 28px;">
        No evaluated assignment is available yet. Your performance summary will update automatically after AI
        evaluation and mentor review.
    </div>
@endif

@if($topicPerformance->isNotEmpty())
    <div class="section-label">Topic Performance</div>
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Topic</th>
                    <th>AI Avg</th>
                    <th>Final Avg</th>
                    <th>Reviewed</th>
                    <th>Grade</th>
                    <th>Status</th>
                    <th>Deadline</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topicPerformance as $topicRow)
                    <tr>
                        <td class="cell-name">{{ $topicRow->topic->title ?? 'N/A' }}</td>
                        <td class="cell-mono">{{ $topicRow->ai_score ?? 'N/A' }}</td>
                        <td class="cell-mono">{{ $topicRow->final_score ?? 'N/A' }}</td>
                        <td class="cell-mono">{{ $topicRow->reviewed_answers }}</td>
                        <td>
                            @if($topicRow->grade)
                                <span class="grade-pill grade-{{ $topicRow->grade }}">{{ $topicRow->grade }}</span>
                            @else
                                <span class="grade-pill grade-none">N/A</span>
                            @endif
                        </td>
                        <td><x-badge :status="$topicRow->status" /></td>
                        <td class="cell-mono">{{ optional($topicRow->deadline)->format('d M Y') ?? 'Not set' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
