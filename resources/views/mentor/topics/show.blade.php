@extends('layouts.mentor')
@section('title', $topic->title)
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 1px solid #e5e5e5; margin-bottom: 28px; }
    .page-title  { font-size: 20px; font-weight: 500; letter-spacing: -0.02em; }
    .page-meta   { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; margin-top: 4px; }
    .back-link   { font-family: 'DM Mono', monospace; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: #888; text-decoration: none; }
    .back-link:hover { color: #1a1a1a; }

    .topic-actions { display: flex; gap: 10px; margin-bottom: 28px; flex-wrap: wrap; }
    .btn-primary { background: #1a1a1a; color: #fff; border: none; border-radius: 2px; padding: 9px 20px; font-size: 13px; font-weight: 500; cursor: pointer; font-family: 'DM Sans', sans-serif; text-decoration: none; transition: background 0.12s; }
    .btn-primary:hover { background: #333; }
    .btn-outline { background: #fff; color: #1a1a1a; border: 1px solid #d4d4d4; border-radius: 2px; padding: 9px 20px; font-size: 13px; text-decoration: none; font-family: 'DM Sans', sans-serif; transition: border-color 0.12s; cursor: pointer; }
    .btn-outline:hover { border-color: #888; }
    .btn-danger  { background: #fff; color: #c0392b; border: 1px solid #e8b8b8; border-radius: 2px; padding: 9px 20px; font-size: 13px; text-decoration: none; font-family: 'DM Sans', sans-serif; transition: border-color 0.12s; cursor: pointer; }
    .btn-danger:hover  { border-color: #c0392b; }

    .badge { display: inline-block; padding: 3px 12px; border-radius: 2px; font-size: 11px; font-family: 'DM Mono', monospace; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 500; }
    .badge-draft        { background: #f5f0e8; color: #92681a; }
    .badge-ai_generated { background: #e8f0f5; color: #1a5092; }
    .badge-reviewed     { background: #f0ebf5; color: #5a1a92; }
    .badge-published    { background: #eaf5e8; color: #1a6a1a; }

    .type-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1px; background: #e5e5e5; border: 1px solid #e5e5e5; border-radius: 2px; overflow: hidden; }
    .type-card { background: #fff; padding: 24px 22px 20px; text-decoration: none; display: block; transition: background 0.12s; position: relative; }
    .type-card:hover { background: #fafafa; }
    .type-name { font-family: 'DM Mono', monospace; font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; color: #aaa; margin-bottom: 10px; }
    .type-count { font-family: 'DM Mono', monospace; font-size: 36px; font-weight: 400; color: #1a1a1a; line-height: 1; margin-bottom: 2px; }
    .type-sublabel { font-family: 'DM Mono', monospace; font-size: 10px; color: #bbb; text-transform: uppercase; letter-spacing: 0.06em; }
    .type-arrow { position: absolute; top: 20px; right: 20px; font-size: 14px; color: #ccc; }

    .topic-info { background: #fff; border: 1px solid #e5e5e5; border-radius: 2px; padding: 20px 24px; margin-bottom: 24px; }
    .info-row   { display: flex; gap: 32px; flex-wrap: wrap; }
    .info-item  { }
    .info-label { font-family: 'DM Mono', monospace; font-size: 10px; color: #aaa; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 4px; }
    .info-value { font-size: 13px; color: #1a1a1a; }

    .empty-types { background: #fff; border: 1px solid #e5e5e5; border-radius: 2px; padding: 56px; text-align: center; font-family: 'DM Mono', monospace; font-size: 13px; color: #aaa; }
</style>

<div class="page-header">
    <div>
        <div class="page-title">{{ $topic->title }}</div>
        <div class="page-meta" style="display:flex;align-items:center;gap:10px;margin-top:6px;">
            <span class="badge badge-{{ $topic->status }}">{{ ucfirst(str_replace('_',' ',$topic->status)) }}</span>
            <span>{{ $topic->questions->count() }} questions total</span>
        </div>
    </div>
    <a href="{{ route('mentor.topics.index') }}" class="back-link">← All Topics</a>
</div>

{{-- Topic info --}}
@if($topic->description)
<div class="topic-info">
    <div class="info-label">Description</div>
    <div class="info-value">{{ $topic->description }}</div>
</div>
@endif

{{-- Action buttons based on status --}}
<div class="topic-actions">
    @if($topic->status === 'draft')
        <form method="POST" action="{{ route('mentor.topics.generateAI', $topic->id) }}" onsubmit="return confirm('Generate AI questions for this topic? This will create questions for all configured types.')">
            @csrf
            <button class="btn-primary">✦ Generate AI Questions</button>
        </form>
        <a href="{{ route('mentor.topics.edit', $topic->id) }}" class="btn-outline">Edit Topic</a>
    @endif

    @if($topic->status === 'ai_generated')
        <form method="POST" action="{{ route('mentor.topics.publish', $topic->id) }}" onsubmit="return confirm('Publish this topic so it can be assigned to interns?')">
            @csrf
            <button class="btn-primary">Publish Topic</button>
        </form>
        <form method="POST" action="{{ route('mentor.topics.generateAI', $topic->id) }}" onsubmit="return confirm('Regenerate questions? Existing AI questions will be deleted.')">
            @csrf
            <button class="btn-outline">Regenerate</button>
        </form>
    @endif

    @if($topic->status === 'published')
        <a href="{{ route('mentor.topics.assign') }}" class="btn-primary">Assign to Intern</a>
    @endif

    @if(in_array($topic->status, ['draft','ai_generated']))
        <form method="POST" action="{{ route('mentor.topics.destroy', $topic->id) }}" onsubmit="return confirm('Permanently delete this topic?')">
            @csrf @method('DELETE')
            <button class="btn-danger">Delete</button>
        </form>
    @endif
</div>

{{-- Question type cards --}}
@php $grouped = $topic->questions->groupBy('type'); @endphp

@if($grouped->isEmpty())
    <div class="empty-types">
        No questions yet.
        @if($topic->status === 'draft')
            Click "Generate AI Questions" above to create questions automatically.
        @endif
    </div>
@else
    <div class="type-grid">
        @foreach($grouped as $type => $qs)
            <a href="{{ route('mentor.topics.questions', [$topic, $type]) }}" class="type-card">
                <div class="type-arrow">→</div>
                <div class="type-name">{{ str_replace('_',' ', $type) }}</div>
                <div class="type-count">{{ count($qs) }}</div>
                <div class="type-sublabel">Questions</div>
            </a>
        @endforeach
    </div>
@endif
@endsection