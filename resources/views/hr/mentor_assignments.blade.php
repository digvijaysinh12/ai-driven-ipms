@extends('layouts.app')
@section('title', 'Assign Mentors')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Assign Mentors</div>
        <div class="page-meta">Assign a mentor to each approved intern</div>
    </div>
</div>

<div class="table-card">
    @forelse($interns as $intern)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #f0f0f0;gap:16px;">
            <div>
                <div class="cell-name" style="font-size:14px;">{{ $intern->name }}</div>
                <div class="cell-mono" style="margin-top:2px;">{{ $intern->email }}</div>
            </div>
            <form method="POST" action="{{ route('hr.assigned.mentor') }}"
                  style="display:flex;gap:8px;align-items:center;">
                @csrf
                <input type="hidden" name="intern_id" value="{{ $intern->id }}">
                <select name="mentor_id" class="form-select" style="min-width:180px;" required>
                    <option value="">Select mentor</option>
                    @foreach($mentors as $mentor)
                        <option value="{{ $mentor->id }}">{{ $mentor->name }}</option>
                    @endforeach
                </select>
                <button class="btn-primary btn-sm">Assign</button>
            </form>
        </div>
    @empty
        <div class="empty-state">No unassigned interns.</div>
    @endforelse
</div>
@endsection