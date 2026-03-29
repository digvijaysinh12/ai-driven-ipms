@extends('layouts.app')
@section('title', 'Intern Progress')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Intern Progress</div>
        <div class="page-meta">Select an intern from the Intern–Mentor Map to view details</div>
    </div>
    <a href="{{ route('hr.intern.mentor.list') }}" class="back-link">← Intern–Mentor Map</a>
</div>

<div style="background:#fff;border:1px solid #e5e5e5;border-radius:2px;padding:28px;">
    <p style="font-size:13.5px;color:#555;line-height:1.7;">
        Monitor intern assignments, submissions, and mentor feedback here.
        Click <strong>View Progress</strong> next to any intern in the Intern–Mentor Map to see their individual progress.
    </p>
</div>
@endsection