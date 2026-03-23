@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="stat-mosaic" style="margin-bottom:1px;">
    <x-stat-card label="Interns"       :value="$internCount" />
    <x-stat-card label="Topics"        :value="$topicCount" />
    <x-stat-card label="Questions"     :value="$questionCount" />
    <x-stat-card label="Published"     :value="$publishedTopics" accent="accent" />
</div>
<div class="stat-mosaic">
    <x-stat-card label="AI Generated"  :value="$aiTopics" />
    <x-stat-card label="Draft"         :value="$draftTopics" />
    <x-stat-card label="Pending Review":value="$pendingReview"  accent="warn" />
    <x-stat-card label="Reviewed"      :value="$reviewedCount"  accent="accent" />
</div>

{{-- Recent Topics --}}
<div class="section-row" style="margin-top:4px;">
    <div class="section-label" style="margin-bottom:0;">Recent Topics</div>
    <a href="{{ route('mentor.topics.index') }}" class="section-link">View all →</a>
</div>
<div class="table-card">
    <table class="data-table">
        <thead>
            <tr><th>Title</th><th>Status</th><th>Questions</th><th>Created</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($recentTopics as $topic)
                <tr>
                    <td class="cell-name">{{ $topic->title }}</td>
                    <td><x-badge :status="$topic->status" /></td>
                    <td class="cell-mono">{{ $topic->questions()->count() }}</td>
                    <td class="cell-mono">{{ $topic->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('mentor.topics.show', $topic->id) }}" class="action-link">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-state">No topics yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Recent Assignments --}}
<div class="section-row">
    <div class="section-label" style="margin-bottom:0;">Recent Assignments</div>
    <a href="{{ route('mentor.assignments') }}" class="section-link">View all →</a>
</div>
<div class="table-card">
    <table class="data-table">
        <thead>
            <tr><th>Intern</th><th>Topic</th><th>Status</th><th>Deadline</th></tr>
        </thead>
        <tbody>
            @forelse($recentAssignments as $asgn)
                <tr>
                    <td class="cell-name">{{ $asgn->intern->name ?? '—' }}</td>
                    <td style="color:#555;">{{ $asgn->topic->title ?? '—' }}</td>
                    <td><x-badge :status="$asgn->status" /></td>
                    <td class="cell-mono">{{ \Carbon\Carbon::parse($asgn->deadline)->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty-state">No assignments yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection