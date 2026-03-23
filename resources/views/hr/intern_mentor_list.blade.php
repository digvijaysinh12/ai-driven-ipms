@extends('layouts.app')
@section('title', 'Intern–Mentor Map')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Intern–Mentor Map</div>
        <div class="page-meta">{{ $assignments->count() }} assignments</div>
    </div>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Intern</th>
                <th>Mentor</th>
                <th>Assigned</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $a)
                <tr>
                    <td class="cell-name">{{ $a->intern->name }}</td>
                    <td class="cell-name">{{ $a->mentor->name }}</td>
                    <td class="cell-mono">{{ \Carbon\Carbon::parse($a->assigned_at)->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('hr.intern.progress.show', $a->intern->id) }}" class="action-link">
                            View Progress
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty-state">No assignments yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection