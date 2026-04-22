@extends('layouts.app')
@section('title', $topic->title)

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ $topic->title }}</div>
        <div class="page-meta" style="display:flex;align-items:center;gap:10px;margin-top:6px;">
            <x-badge :status="$topic->status" />
            <span>{{ $topic->questions->count() }} questions total</span>
        </div>
    </div>
    <a href="{{ route('user.mentor.tasks.index') }}" class="back-link">← All Topics</a>
</div>

@if($topic->description)
    <div style="background:#fff;border:1px solid #e5e5e5;border-radius:2px;padding:20px 24px;margin-bottom:24px;">
        <div class="section-label" style="margin-bottom:4px;">Description</div>
        <div style="font-size:13px;color:#1a1a1a;">{{ $topic->description }}</div>
    </div>
@endif

{{-- Action buttons based on topic status --}}
<div class="action-group" style="margin-bottom:28px;">
    @if($topic->status === 'draft')
        <form method="POST" action="{{ route('user.mentor.tasks.generateQuestions', $topic->id) }}"
              onsubmit="return confirm('Generate AI questions for this topic?')">
            @csrf
            <button class="btn-primary">✦ Generate AI Questions</button>
        </form>
        <a href="{{ route('user.mentor.tasks.show', $topic->id) }}" class="btn-outline">Edit Topic</a>
    @endif

    @if($topic->status === 'ai_generated')
        <form method="POST" action="{{ route('user.mentor.tasks.update', $topic->id) }}"
              onsubmit="return confirm('Publish this topic so it can be assigned to interns?')">
            @csrf
            <button class="btn-primary">Publish Topic</button>
        </form>
        <form method="POST" action="{{ route('user.mentor.tasks.generateQuestions', $topic->id) }}"
              onsubmit="return confirm('Regenerate? Existing AI questions will be deleted.')">
            @csrf
            <button class="btn-outline">Regenerate</button>
        </form>
    @endif

    @if($topic->status === 'published')
        <a href="{{ route('user.mentor.tasks.create') }}" class="btn-primary">Assign to Intern</a>
    @endif

    @if(in_array($topic->status, ['draft', 'ai_generated']))
        <form method="POST" action="{{ route('user.mentor.tasks.update', $topic->id) }}"
              onsubmit="return confirm('Permanently delete this topic?')">
            @csrf @method('DELETE')
            <button class="btn-danger">Delete</button>
        </form>
    @endif
</div>

{{-- Question type cards --}}
@php $grouped = $topic->questions->groupBy('type'); @endphp

@if($grouped->isEmpty())
    <div style="background:#fff;border:1px solid #e5e5e5;border-radius:2px;padding:56px;text-align:center;font-family:'DM Mono',monospace;font-size:13px;color:#aaa;">
        No questions yet.
        @if($topic->status === 'draft')
            Click "Generate AI Questions" above to create questions automatically.
        @endif
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1px;background:#e5e5e5;border:1px solid #e5e5e5;border-radius:2px;overflow:hidden;">
        @foreach($grouped as $type => $qs)
            <a href="{{ route('user.mentor.tasks.show', [$topic, $type]) }}"
               style="background:#fff;padding:24px 22px 20px;text-decoration:none;display:block;position:relative;transition:background 0.12s;">
                <div style="position:absolute;top:20px;right:20px;font-size:14px;color:#ccc;">→</div>
                <div class="module-type">{{ str_replace('_', ' ', $type) }}</div>
                <div class="module-count">{{ count($qs) }}</div>
                <div class="module-count-label">Questions</div>
            </a>
        @endforeach
    </div>
@endif
@endsection
