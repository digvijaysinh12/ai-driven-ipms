@extends('layouts.mentor')
@section('title', 'Topics')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; padding-bottom: 20px; border-bottom: 1px solid #e5e5e5; margin-bottom: 24px; }
    .page-title { font-size: 20px; font-weight: 500; letter-spacing: -0.02em; }
    .page-meta  { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; margin-top: 4px; }
    .btn-create { background: #1a1a1a; color: #fff; padding: 9px 18px; border-radius: 2px; font-size: 13px; font-weight: 500; text-decoration: none; transition: background 0.12s; }
    .btn-create:hover { background: #333; }

    .table-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 2px; overflow: hidden; }
    .data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .data-table th { text-align: left; padding: 12px 16px; font-size: 10px; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; color: #888; font-family: 'DM Mono', monospace; border-bottom: 2px solid #e5e5e5; }
    .data-table td { padding: 15px 16px; border-bottom: 1px solid #f0f0f0; color: #1a1a1a; vertical-align: middle; }
    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover td { background: #fafafa; }

    .badge { display: inline-block; padding: 2px 10px; border-radius: 2px; font-size: 10px; font-family: 'DM Mono', monospace; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 500; }
    .badge-draft        { background: #f5f0e8; color: #92681a; }
    .badge-ai_generated { background: #e8f0f5; color: #1a5092; }
    .badge-reviewed     { background: #f0ebf5; color: #5a1a92; }
    .badge-published    { background: #eaf5e8; color: #1a6a1a; }

    .mono { font-family: 'DM Mono', monospace; font-size: 12px; color: #555; }
    .topic-title { font-weight: 500; font-size: 13.5px; }
    .topic-desc  { font-size: 12px; color: #999; margin-top: 2px; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .action-cell { display: flex; align-items: center; gap: 14px; }
    .action-link { font-size: 12px; color: #555; text-decoration: underline; text-underline-offset: 2px; background: none; border: none; cursor: pointer; font-family: 'DM Sans', sans-serif; padding: 0; transition: color 0.12s; }
    .action-link:hover { color: #1a1a1a; }
    .btn-sm { background: #1a1a1a; color: #fff; padding: 5px 14px; border-radius: 2px; font-size: 12px; font-weight: 500; text-decoration: none; transition: background 0.12s; border: none; cursor: pointer; font-family: 'DM Sans', sans-serif; }
    .btn-sm:hover { background: #333; }
    .btn-sm-outline { background: #fff; color: #1a1a1a; border: 1px solid #d4d4d4; padding: 5px 14px; border-radius: 2px; font-size: 12px; text-decoration: none; transition: border-color 0.12s; }
    .btn-sm-outline:hover { border-color: #888; }

    .empty-state { padding: 56px; text-align: center; font-family: 'DM Mono', monospace; font-size: 13px; color: #aaa; }
</style>

<div class="page-header">
    <div>
        <div class="page-title">Topics</div>
        <div class="page-meta">{{ $topics->count() }} total</div>
    </div>
    <a href="{{ route('mentor.topics.create') }}" class="btn-create">+ New Topic</a>
</div>

<div class="table-card">
    @if($topics->isEmpty())
        <div class="empty-state">No topics yet.<br>Create your first topic to start generating questions.</div>
    @else
    <table class="data-table">
        <thead><tr>
            <th>Title</th><th>Status</th><th>Questions</th><th>Created</th><th>Actions</th>
        </tr></thead>
        <tbody>
        @foreach($topics as $topic)
        <tr>
            <td>
                <div class="topic-title">{{ $topic->title }}</div>
                @if($topic->description)
                    <div class="topic-desc">{{ $topic->description }}</div>
                @endif
            </td>
            <td>
                <span class="badge badge-{{ $topic->status }}">
                    {{ ucfirst(str_replace('_',' ',$topic->status)) }}
                </span>
            </td>
            <td class="mono">{{ $topic->questions_count }}</td>
            <td class="mono">{{ $topic->created_at->format('d M Y') }}</td>
            <td>
                <div class="action-cell">
                    <a href="{{ route('mentor.topics.show', $topic->id) }}" class="action-link">View</a>

                    @if($topic->status === 'draft')
                        <form method="POST" action="{{ route('mentor.topics.generateAI', $topic->id) }}" style="display:inline" onsubmit="return confirm('Generate AI questions for this topic?')">
                            @csrf
                            <button class="action-link" style="color:#1a5092;">Generate AI</button>
                        </form>
                    @endif

                    @if($topic->status === 'ai_generated')
                        <form method="POST" action="{{ route('mentor.topics.publish', $topic->id) }}" style="display:inline" onsubmit="return confirm('Publish this topic?')">
                            @csrf
                            <button class="btn-sm">Publish</button>
                        </form>
                    @endif

                    @if($topic->status === 'published')
                        <a href="{{ route('mentor.topics.assign') }}" class="btn-sm">Assign</a>
                    @endif

                    @if(in_array($topic->status, ['draft','ai_generated']))
                        <form method="POST" action="{{ route('mentor.topics.destroy', $topic->id) }}" style="display:inline" onsubmit="return confirm('Delete this topic?')">
                            @csrf @method('DELETE')
                            <button class="action-link" style="color:#c0392b;">Delete</button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection