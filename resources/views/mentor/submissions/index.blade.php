@extends('layouts.app')
@section('title', 'Submissions')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Submissions</div>
        <div class="page-meta">Pending and completed intern submissions</div>
    </div>
</div>

<div class="table-card">
    @if($submissions->isEmpty())
        <div class="empty-state">No submissions yet.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th><th>Intern</th><th>Question</th><th>Type</th><th>Status</th><th>Date</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($submissions as $i => $sub)
                    <tr>
                        <td class="cell-mono">{{ $i + 1 }}</td>
                        <td class="cell-name">{{ $sub->intern->name ?? '—' }}</td>
                        <td>
                            <div style="max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px;">
                                {{ $sub->question->problem_statement ?? '—' }}
                            </div>
                        </td>
                        <td class="cell-mono">{{ str_replace('_', ' ', $sub->question->type ?? '') }}</td>
                        <td><x-badge :status="$sub->status" /></td>
                        <td class="cell-mono">{{ $sub->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('mentor.submissions.show', $sub->id) }}" class="action-link">Review</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection