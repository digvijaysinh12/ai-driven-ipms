@extends('layouts.app')
@section('title', 'My Performance')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">My Performance</div>
<<<<<<< HEAD
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
=======
        <div class="page-meta">Overall score breakdown</div>
    </div>
</div>

{{-- ── Score breakdown ── --}}
<div class="performance-breakdown">
    <div class="perf-cell">
        <div class="perf-label">AI Score</div>
        <div class="perf-value">{{ $aiScore ?? '—' }}</div>
        @if($aiScore !== null)
            <div class="perf-bar">
                <div class="perf-fill {{ $aiScore >= 70 ? 'good' : ($aiScore < 40 ? 'warn' : '') }}"
                     style="width:{{ $aiScore }}%"></div>
            </div>
        @endif
    </div>
    <div class="perf-cell">
        <div class="perf-label">Mentor Score</div>
        <div class="perf-value">{{ $mentorScore ?? '—' }}</div>
        @if($mentorScore !== null)
            <div class="perf-bar">
                <div class="perf-fill {{ $mentorScore >= 70 ? 'good' : '' }}"
                     style="width:{{ $mentorScore }}%"></div>
            </div>
        @endif
    </div>
    <div class="perf-cell">
        <div class="perf-label">Attendance</div>
        <div class="perf-value">{{ $attendanceScore ?? '—' }}%</div>
        @if($attendanceScore !== null)
            <div class="perf-bar">
                <div class="perf-fill {{ $attendanceScore >= 75 ? 'good' : 'warn' }}"
                     style="width:{{ $attendanceScore }}%"></div>
            </div>
        @endif
    </div>
    <div class="perf-cell">
        <div class="perf-label">Time Efficiency</div>
        <div class="perf-value">{{ $timeScore ?? '—' }}%</div>
        @if($timeScore !== null)
            <div class="perf-bar">
                <div class="perf-fill" style="width:{{ $timeScore }}%"></div>
            </div>
        @endif
    </div>
</div>

{{-- ── Final grade card ── --}}
@if($finalGrade)
    @php
        $gc = [
            'A' => ['#f0faf0','#b8ddb8','#1a5a1a','Excellent'],
            'B' => ['#f0f5ff','#b8ccee','#1a3a7a','Good'],
            'C' => ['#fffbf0','#e8d8a0','#7a5a00','Average'],
            'D' => ['#fff5f0','#e8c8b8','#7a3a1a','Below Average'],
            'E' => ['#fdf0f0','#e8b8b8','#7a1a1a','Needs Improvement'],
        ][$finalGrade] ?? ['#fdf0f0','#e8b8b8','#7a1a1a','Needs Improvement'];
    @endphp
    <div class="section-label" style="margin-bottom:14px;">Final Grade</div>
    <div class="final-grade-card"
         style="background:{{ $gc[0] }};border-color:{{ $gc[1] }};">
        <div>
            <div class="grade-letter-big" style="color:{{ $gc[2] }};">{{ $finalGrade }}</div>
            <div style="font-family:'DM Mono',monospace;font-size:11px;color:{{ $gc[2] }};
                        text-transform:uppercase;letter-spacing:0.08em;margin-top:4px;">
                {{ $gc[3] }}
            </div>
        </div>
        <div>
            <div class="section-label" style="margin-bottom:6px;">Final Score</div>
            <div style="font-family:'DM Mono',monospace;font-size:32px;
                        font-weight:400;color:#1a1a1a;">
                {{ $finalScore ?? '—' }}
            </div>
            @if($mentorFeedback)
                <div class="section-label" style="margin:14px 0 6px;">Mentor Feedback</div>
                <div style="font-size:13.5px;color:#444;line-height:1.7;">
                    {{ $mentorFeedback }}
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
                </div>
            @endif
        </div>
    </div>
<<<<<<< HEAD
@else
    <div class="table-card" style="padding: 28px; text-align: center; font-family: 'DM Mono', monospace; color: #888; margin-bottom: 28px;">
        No evaluated assignment is available yet. Your performance summary will update automatically after AI
        evaluation and mentor review.
    </div>
@endif

@if($topicPerformance->isNotEmpty())
    <div class="section-label">Topic Performance</div>
=======

@else
    <div style="background:#fff;border:1px solid #e5e5e5;border-radius:2px;
                padding:28px;font-family:'DM Mono',monospace;
                font-size:13px;color:#aaa;text-align:center;">
        Final grade will appear here once your mentor completes the review.
    </div>
@endif

{{-- ── Per-topic scores ── --}}
@if(isset($topicScores) && $topicScores->isNotEmpty())
    <div class="section-label" style="margin:28px 0 14px;">Topic Scores</div>
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Topic</th>
<<<<<<< HEAD
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
=======
                    <th>AI Score</th>
                    <th>Mentor Score</th>
                    <th>Grade</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topicScores as $ts)
                    <tr>
                        <td class="cell-name">{{ $ts->topic->title ?? '—' }}</td>
                        <td class="cell-mono">{{ $ts->ai_score ?? '—' }}</td>
                        <td class="cell-mono">{{ $ts->mentor_score ?? '—' }}</td>
                        <td>
                            @if($ts->grade)
                                <span class="grade-pill grade-{{ $ts->grade }}">{{ $ts->grade }}</span>
                            @else
                                <span class="grade-pill grade-none">—</span>
                            @endif
                        </td>
                        <td><x-badge :status="$ts->status" /></td>
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
<<<<<<< HEAD
@endsection
=======
@endsection
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
