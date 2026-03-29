@extends('layouts.app')
<<<<<<< HEAD
@section('title', 'Mentor Dashboard')
=======
@section('title', 'Dashboard')
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26

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

<<<<<<< HEAD
<div class="container-fluid">

    {{-- 🔹 Top Stats --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <x-stat-card label="Interns" :value="$internCount" icon="fa-users"/>
        </div>
        <div class="col-md-3">
            <x-stat-card label="Topics" :value="$topicCount" icon="fa-book"/>
        </div>
        <div class="col-md-3">
            <x-stat-card label="Questions" :value="$questionCount" icon="fa-question-circle"/>
        </div>
        <div class="col-md-3">
            <x-stat-card label="Published" :value="$publishedTopics" accent="accent" icon="fa-check"/>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <x-stat-card label="AI Generated" :value="$aiTopics" icon="fa-robot"/>
        </div>
        <div class="col-md-3">
            <x-stat-card label="Draft" :value="$draftTopics" icon="fa-edit"/>
        </div>
        <div class="col-md-3">
            <x-stat-card label="Pending Review" :value="$pendingReview" accent="warn" icon="fa-clock"/>
        </div>
        <div class="col-md-3">
            <x-stat-card label="Reviewed" :value="$reviewedCount" accent="accent" icon="fa-check-double"/>
        </div>
    </div>

    {{-- 🔹 Quick Actions --}}
    <div class="mb-4 d-flex gap-2">
        <a href="{{ route('mentor.topics.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> Create Topic
        </a>
        <a href="{{ route('mentor.assignments') }}" class="btn btn-outline-dark">
            <i class="fa fa-tasks"></i> View Assignments
        </a>
    </div>

    {{-- 🔹 Recent Topics --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between">
            <h6 class="mb-0">Recent Topics</h6>
            <a href="{{ route('mentor.topics.index') }}">View all →</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Questions</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTopics as $topic)
                        <tr>
                            <td><strong>{{ $topic->title }}</strong></td>

                            <td>
                                <x-badge :status="$topic->status" />
                            </td>

                            <td>{{ $topic->questions()->count() }}</td>

                            <td>{{ $topic->created_at->format('d M Y') }}</td>

                            <td>
                                <a href="{{ route('mentor.topics.show', $topic->id) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No topics created yet 🚀
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 🔹 Recent Assignments --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between">
            <h6 class="mb-0">Recent Assignments</h6>
            <a href="{{ route('mentor.assignments') }}">View all →</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Intern</th>
                        <th>Topic</th>
                        <th>Status</th>
                        <th>Deadline</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAssignments as $asgn)
                        <tr>
                            <td><strong>{{ $asgn->intern->name ?? '—' }}</strong></td>

                            <td>{{ $asgn->topic->title ?? '—' }}</td>

                            <td>
                                <x-badge :status="$asgn->status" />
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($asgn->deadline)->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No assignments yet 📭
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

=======
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
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
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