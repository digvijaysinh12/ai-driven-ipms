@extends('layouts.app')
@section('title', 'Reviews')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">Submission history</div>
        <h1 class="page-shell-title">Reviews</h1>
        <p class="page-shell-subtitle">
            View your submitted answers, grading progress, and mentor-reviewed results.
        </p>
    </div>
</div>

<div class="summary-strip">
    <div class="summary-card">
        <div class="summary-label">Assignments</div>
        <div class="summary-value">{{ $assignments->count() }}</div>
        <div class="summary-note">Total task assignments on record.</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Submissions</div>
        <div class="summary-value">{{ $totalSubmissions }}</div>
        <div class="summary-note">Answers saved or submitted across all tasks.</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Reviewed</div>
        <div class="summary-value">{{ $reviewedCount }}</div>
        <div class="summary-note">Answers already reviewed by your mentor.</div>
    </div>
</div>

<div class="page-shell-header mt-5">
    <div class="page-shell-copy">
        <h2 class="page-shell-title" style="font-size: 20px;">Task Evaluations</h2>
        <p class="page-shell-subtitle">Holistic feedback from our AI Senior Mentor on your full assignments.</p>
    </div>
</div>

<div class="assignment-grid mt-3 mb-5" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;">
    @forelse($assignments as $assignment)
        @if($assignment->status === 'evaluated' || $assignment->status === 'submitted')
            <div class="ui-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <span class="table-chip">{{ $assignment->topic->title }}</span>
                    <x-badge :status="$assignment->status" />
                </div>
                
                @if($assignment->status === 'evaluated')
                    <div style="display: flex; gap: 16px; align-items: flex-start; margin-top: 16px;">
                        <div class="score-circle" style="background: var(--ui-primary); color: #fff; width: 48px; height: 48px; font-size: 18px; flex-shrink: 0;">
                            {{ $assignment->grade }}
                        </div>
                        <div>
                            <div class="form-label" style="font-size: 11px; margin-bottom: 4px;">Mentor Feedback</div>
                            <p style="font-size: 13px; line-height: 1.5; color: var(--ui-text-soft);">{{ $assignment->feedback }}</p>
                            
                            @if(!empty($assignment->weak_areas))
                                <div style="margin-top: 12px;">
                                    <div class="form-label" style="font-size: 10px; margin-bottom: 4px;">Weak Areas</div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                        @foreach($assignment->weak_areas as $area)
                                            <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600;">
                                                {{ $area }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="empty-state" style="padding: 20px 0;">
                        Wait a moment! Your assignment is being evaluated by our Senior Mentor.
                    </div>
                @endif
            </div>
        @endif
    @empty
        <div class="ui-card text-center p-4">No assignments submitted for evaluation yet.</div>
    @endforelse
</div>

<div class="page-shell-header">
    <div class="page-shell-copy">
        <h2 class="page-shell-title" style="font-size: 20px;">Answer History</h2>
        <p class="page-shell-subtitle">Individual submission records for all tasks.</p>
    </div>
</div>

<x-ui.table>
    @if($submissions->isEmpty())
        <tbody>
            <tr>
                <td colspan="5" class="empty-state">No answers saved yet. Open a task to start solving questions.</td>
            </tr>
        </tbody>
    @else
        <thead>
            <tr>
                <th>Task</th>
                <th>Question Type</th>
                <th>Status</th>
                <th>Final Score</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($submissions as $submission)
                <tr>
                    <td>
                        <div class="table-title">{{ $submission->question?->topic?->title ?? 'Untitled task' }}</div>
                        <div class="table-subtitle">{{ \Illuminate\Support\Str::limit($submission->question?->problem_statement ?? 'No question text available.', 80) }}</div>
                    </td>
                    <td><span class="table-chip">{{ str_replace('_', ' ', $submission->question?->type ?? 'unknown') }}</span></td>
                    <td><x-badge :status="$submission->status" /></td>
                    <td class="cell-mono">{{ $submission->final_score ?? 'Pending' }}</td>
                    <td class="cell-mono">{{ $submission->updated_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    @endif
</x-ui.table>

{{ $submissions->links() }}
@endsection
