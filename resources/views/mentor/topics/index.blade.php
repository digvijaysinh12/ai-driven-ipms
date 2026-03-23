@extends('layouts.app')
@section('title', 'Topics')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Topics</div>
        <div class="page-meta">{{ $topics->total() }} total</div>
    </div>
    <a href="{{ route('mentor.topics.create') }}" class="btn-primary">+ New Topic</a>
</div>

<div class="table-card">
    @if($topics->isEmpty())
        <div class="empty-state">No topics yet.<br>Create your first topic to start generating questions.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th><th>Status</th><th>Questions</th><th>Created</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topics as $topic)
                    <tr>
                        <td>
                            <div class="cell-name">{{ $topic->title }}</div>
                            @if($topic->description)
                                <div class="cell-mono" style="margin-top:2px;max-width:320px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $topic->description }}
                                </div>
                            @endif
                        </td>
                        <td><x-badge :status="$topic->status" /></td>
                        <td class="cell-mono">{{ $topic->questions_count }}</td>
                        <td class="cell-mono">{{ $topic->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('mentor.topics.show', $topic->id) }}" class="action-link">View</a>

                                @if($topic->status === 'draft')
                                    <a href="{{ route('mentor.topics.edit', $topic->id) }}" class="action-link">Edit</a>
                                    <form method="POST" action="{{ route('mentor.topics.generateAI', $topic->id) }}"
                                          onsubmit="return confirm('Start AI question generation for this topic? This may take a few minutes.')">
                                        @csrf
                                        <button class="action-link" style="color:#1a5092;">Generate AI</button>
                                    </form>
                                @endif

                                @if($topic->status === 'ai_generated')
                                    <form method="POST" action="{{ route('mentor.topics.publish', $topic->id) }}"
                                          onsubmit="return confirm('Publish this topic?')">
                                        @csrf
                                        <button class="btn-primary btn-sm">Publish</button>
                                    </form>
                                @endif

                                @if($topic->status === 'published')
                                    <a href="{{ route('mentor.topics.assign') }}" class="btn-primary btn-sm">Assign</a>
                                @endif

                                @if(in_array($topic->status, ['draft', 'ai_generated']))
                                    <form method="POST" action="{{ route('mentor.topics.destroy', $topic->id) }}"
                                          onsubmit="return confirm('Delete this topic permanently?')">
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

{{ $topics->links() }}
@endsection