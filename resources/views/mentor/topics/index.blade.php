@extends('layouts.app')
@section('title', 'Task Management')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">Mentor workspace</div>
        <h1 class="page-shell-title">Task Management</h1>
        <p class="page-shell-subtitle">
            Create task sets, review AI generation progress, and publish them once they are ready for interns.
        </p>
    </div>

    <div class="page-shell-actions">
        <x-ui.button data-modal-open="create-task-modal">+ Create Task</x-ui.button>
    </div>
</div>

<div class="summary-strip">
    <div class="summary-card">
        <div class="summary-label">Total Tasks</div>
        <div class="summary-value">{{ $stats['total'] }}</div>
        <div class="summary-note">All tasks created under your mentorship.</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Published</div>
        <div class="summary-value">{{ $stats['published'] }}</div>
        <div class="summary-note">Ready to assign to interns.</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Needs Attention</div>
        <div class="summary-value">{{ $stats['needs_attention'] }}</div>
        <div class="summary-note">Draft or generated tasks waiting for your action.</div>
    </div>
</div>

<x-ui.table>
    @if($topics->isEmpty())
        <tbody>
            <tr>
                <td colspan="4" class="empty-state">No tasks yet. Use Create Task to add your first task.</td>
            </tr>
        </tbody>
    @else
        <thead>
            <tr>
                <th>Title</th>
                <th>Difficulty</th>
                <th>Created Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topics as $topic)
                <tr>
                    <td>
                        <div class="table-title">{{ $topic->title }}</div>
                        <div class="table-subtitle">{{ \Illuminate\Support\Str::limit($topic->description ?: 'No description added yet.', 120) }}</div>
                    </td>
                    <td><x-badge :status="$topic->difficulty_label" /></td>
                    <td class="cell-mono">{{ $topic->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('user.mentor.tasks.show', $topic->id) }}" class="action-link">Open</a>

                            @if($topic->status === 'draft')
                                <a href="{{ route('user.mentor.tasks.show', $topic->id) }}" class="action-link">Edit</a>
                                <form method="POST" action="{{ route('user.mentor.tasks.generateQuestions', $topic->id) }}" onsubmit="return confirm('Generate AI questions for this task now?')">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" size="sm">Generate AI</x-ui.button>
                                </form>
                            @endif

                            @if($topic->status === 'ai_generated')
                                <form method="POST" action="{{ route('user.mentor.tasks.update', $topic->id) }}" onsubmit="return confirm('Publish this task for intern assignment?')">
                                    @csrf
                                    <x-ui.button type="submit" size="sm">Publish</x-ui.button>
                                </form>
                            @endif

                            @if($topic->status === 'published')
                                <x-ui.button :href="route('user.mentor.tasks.create')" variant="secondary" size="sm">Assign</x-ui.button>
                            @endif

                            @if(in_array($topic->status, ['draft', 'ai_generated']))
                                <form method="POST" action="{{ route('user.mentor.tasks.update', $topic->id) }}" onsubmit="return confirm('Delete this task permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-link text-danger-link">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    @endif
</x-ui.table>

{{ $topics->links() }}

<x-ui.modal id="create-task-modal" title="Create Task" subtitle="Add a task shell with a title, short description, and target difficulty.">
    <form method="POST" action="{{ route('user.mentor.tasks.store') }}">
        @csrf

        <x-ui.input label="Title" type="text" name="title" :value="old('title')" placeholder="Laravel API task" required />
        <x-ui.textarea label="Description" name="description" rows="5" placeholder="Describe what the intern should practice or solve...">{{ old('description') }}</x-ui.textarea>
        <x-ui.select label="Difficulty" name="difficulty" :options="['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard']" :selected="old('difficulty', 'medium')" />

        <div class="form-actions">
            <x-ui.button type="submit">Create Task</x-ui.button>
            <x-ui.button variant="secondary" data-modal-close="create-task-modal">Cancel</x-ui.button>
        </div>
    </form>
</x-ui.modal>
@endsection
