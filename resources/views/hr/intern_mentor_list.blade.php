@extends('layouts.hr')

@section('title', 'Intern–Mentor Mapping')

@section('content')

<style>
    .page-header {
        padding-bottom: 18px;
        border-bottom: 1px solid #e5e5e5;
        margin-bottom: 24px;
    }

    .page-title { font-size: 18px; font-weight: 500; letter-spacing: -0.01em; }

    .page-meta {
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        color: #aaa;
        margin-top: 3px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
    }

    .data-table thead tr { border-bottom: 2px solid #e5e5e5; }

    .data-table th {
        text-align: left;
        padding: 12px 16px;
        font-size: 10px;
        font-weight: 500;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #888;
        font-family: 'DM Mono', monospace;
    }

    .data-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f0f0f0;
        color: #1a1a1a;
        vertical-align: middle;
    }

    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover td { background: #fafafa; }

    .cell-name { font-weight: 500; font-size: 13.5px; }

    .cell-date {
        font-family: 'DM Mono', monospace;
        font-size: 12px;
        color: #888;
    }

    .action-link {
        font-size: 13px;
        color: #1a1a1a;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .action-link:hover { color: #555; }
</style>

<div class="page-header">
    <div class="page-title">Intern–Mentor Mapping</div>
    <div class="page-meta">{{ $assignments->count() }} assignments</div>
</div>

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
        @foreach($assignments as $a)
            <tr>
                <td class="cell-name">{{ $a->intern->name }}</td>
                <td class="cell-name">{{ $a->mentor->name }}</td>
                <td class="cell-date">{{ \Carbon\Carbon::parse($a->assigned_at)->format('d M Y') }}</td>
                <td>
                    <a href="/hr/intern-progress/{{ $a->intern->id }}" class="action-link">View Progress</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection