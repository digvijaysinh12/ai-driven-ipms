@extends('layouts.mentor')
@section('title', 'Dashboard')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');
    .stat-mosaic {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1px;
        background: #e5e5e5;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 1px;
    }
    .stat-mosaic-row2 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1px;
        background: #e5e5e5;
        border: 1px solid #e5e5e5;
        border-top: none;
        border-radius: 0 0 2px 2px;
        overflow: hidden;
        margin-bottom: 32px;
    }
    .stat-cell { background: #fff; padding: 22px 24px; }
    .stat-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px; letter-spacing: 0.08em;
        text-transform: uppercase; color: #aaa;
        margin-bottom: 8px;
    }
    .stat-value {
        font-family: 'DM Mono', monospace;
        font-size: 32px; font-weight: 400;
        letter-spacing: -0.03em; color: #1a1a1a;
        line-height: 1;
    }
    .stat-value.accent { color: #1a6a1a; }
    .stat-value.warn   { color: #92681a; }

    .section-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }
    .section-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px; letter-spacing: 0.1em;
        text-transform: uppercase; color: #aaa;
    }
    .section-link {
        font-family: 'DM Mono', monospace;
        font-size: 10px; letter-spacing: 0.06em;
        text-transform: uppercase; color: #888;
        text-decoration: none;
    }
    .section-link:hover { color: #1a1a1a; }

    .table-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 28px;
    }
    .data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .data-table th {
        text-align: left; padding: 12px 16px;
        font-size: 10px; font-weight: 500;
        letter-spacing: 0.08em; text-transform: uppercase;
        color: #888; font-family: 'DM Mono', monospace;
        border-bottom: 1px solid #e5e5e5;
    }
    .data-table td {
        padding: 13px 16px;
        border-bottom: 1px solid #f0f0f0;
        color: #1a1a1a; vertical-align: middle;
    }
    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover td { background: #fafafa; }
    .badge {
        display: inline-block; padding: 2px 8px;
        border-radius: 2px; font-size: 10px;
        font-family: 'DM Mono', monospace;
        letter-spacing: 0.05em; text-transform: uppercase;
    }
    .badge-draft        { background: #f5f0e8; color: #92681a; }
    .badge-ai_generated { background: #e8f0f5; color: #1a5092; }
    .badge-published    { background: #eaf5e8; color: #1a6a1a; }
    .badge-pending      { background: #f0f0f0; color: #888; }
    .badge-reviewed     { background: #eaf5e8; color: #1a6a1a; }
    .mono { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; }
    .tlink {
        font-size: 13px; color: #1a1a1a;
        text-decoration: underline; text-underline-offset: 2px;
    }
    .tlink:hover { color: #555; }
    .empty-row td {
        padding: 32px 16px; text-align: center;
        font-family: 'DM Mono', monospace;
        font-size: 12px; color: #ccc;
    }
</style>

{{-- Stat mosaic row 1 --}}
<div class="stat-mosaic">
    <div class="stat-cell">
        <div class="stat-label">Interns</div>
        <div class="stat-value">{{ $internCount }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Topics</div>
        <div class="stat-value">{{ $topicCount }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Questions</div>
        <div class="stat-value">{{ $questionCount }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Published</div>
        <div class="stat-value accent">{{ $publishedTopics }}</div>
    </div>
</div>
{{-- row 2 --}}
<div class="stat-mosaic-row2">
    <div class="stat-cell">
        <div class="stat-label">AI Generated</div>
        <div class="stat-value">{{ $aiTopics }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Draft</div>
        <div class="stat-value">{{ $draftTopics }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Pending Review</div>
        <div class="stat-value warn">{{ $pendingReview }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Reviewed</div>
        <div class="stat-value accent">{{ $reviewedCount }}</div>
    </div>
</div>

{{-- Recent Topics --}}
<div class="section-row">
    <div class="section-label">Recent Topics</div>
    <a href="{{ route('mentor.topics.index') }}" class="section-link">View all →</a>
</div>
<div class="table-card" style="margin-bottom: 28px;">
    <table class="data-table">
        <thead><tr>
            <th>Title</th><th>Status</th><th>Questions</th><th>Created</th><th></th>
        </tr></thead>
        <tbody>
        @forelse($recentTopics as $topic)
            <tr>
                <td style="font-weight:500;">{{ $topic->title }}</td>
                <td><span class="badge badge-{{ $topic->status }}">{{ ucfirst(str_replace('_',' ',$topic->status)) }}</span></td>
                <td class="mono">{{ $topic->questions()->count() }}</td>
                <td class="mono">{{ $topic->created_at->format('d M Y') }}</td>
                <td><a href="{{ route('mentor.topics.show', $topic->id) }}" class="tlink">View</a></td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="5">No topics yet</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Recent Assignments --}}
<div class="section-row">
    <div class="section-label">Recent Assignments</div>
    <a href="{{ route('mentor.assignments') }}" class="section-link">View all →</a>
</div>
<div class="table-card">
    <table class="data-table">
        <thead><tr>
            <th>Intern</th><th>Topic</th><th>Status</th><th>Deadline</th>
        </tr></thead>
        <tbody>
        @forelse($recentAssignments as $asgn)
            <tr>
                <td>{{ $asgn->intern->name ?? '—' }}</td>
                <td style="color:#555;">{{ $asgn->topic->title ?? '—' }}</td>
                <td><span class="badge badge-{{ $asgn->status === 'assigned' ? 'pending' : 'published' }}">{{ ucfirst($asgn->status) }}</span></td>
                <td class="mono">{{ \Carbon\Carbon::parse($asgn->deadline)->format('d M Y') }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="4">No assignments yet</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection