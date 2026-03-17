@extends('layouts.hr')

@section('title', 'Mentor Assignment')

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

    .assign-list {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
    }

    .assign-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #f0f0f0;
        gap: 16px;
    }

    .assign-row:last-child { border-bottom: none; }

    .intern-name { font-size: 14px; font-weight: 500; color: #1a1a1a; }

    .intern-email {
        font-family: 'DM Mono', monospace;
        font-size: 12px;
        color: #888;
        margin-top: 2px;
    }

    .assign-form {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .form-select {
        border: 1px solid #d4d4d4;
        border-radius: 2px;
        padding: 7px 12px;
        font-size: 13px;
        font-family: 'DM Sans', sans-serif;
        color: #1a1a1a;
        background: #fafafa;
        outline: none;
        appearance: none;
        min-width: 180px;
        transition: border-color 0.12s;
    }

    .form-select:focus { border-color: #1a1a1a; background: #fff; }

    .btn-assign {
        background: #1a1a1a;
        color: #fff;
        border: none;
        border-radius: 2px;
        padding: 7px 16px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        white-space: nowrap;
        transition: background 0.12s;
    }

    .btn-assign:hover { background: #333; }

    .empty-state {
        padding: 40px 24px;
        text-align: center;
        font-size: 13px;
        color: #aaa;
        font-family: 'DM Mono', monospace;
    }
</style>

<div class="page-header">
    <div class="page-title">Mentor Assignment</div>
    <div class="page-meta">Assign mentors to interns</div>
</div>

<div class="assign-list">
    @forelse($interns as $intern)
        <div class="assign-row">
            <div>
                <div class="intern-name">{{ $intern->name }}</div>
                <div class="intern-email">{{ $intern->email }}</div>
            </div>
            <form method="POST" action="{{ route('hr.assigned.mentor') }}" class="assign-form">
                @csrf
                <input type="hidden" name="intern_id" value="{{ $intern->id }}">
                <select name="mentor_id" class="form-select" required>
                    <option value="">Select mentor</option>
                    @foreach($mentors as $mentor)
                        <option value="{{ $mentor->id }}">{{ $mentor->name }}</option>
                    @endforeach
                </select>
                <button class="btn-assign">Assign</button>
            </form>
        </div>
    @empty
        <div class="empty-state">No interns available.</div>
    @endforelse
</div>

@endsection