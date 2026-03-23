@extends('layouts.app')
@section('title', 'Edit Topic')

@section('content')
<div class="page-header">
    <div class="page-title">Edit Topic</div>
    <a href="{{ route('mentor.topics.show', $topic->id) }}" class="back-link">← Back to Topic</a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('mentor.topics.update', $topic->id) }}">
        @csrf @method('PUT')

        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" value="{{ old('title', $topic->title) }}"
                   class="form-input" required>
            @error('title') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-textarea">{{ old('description', $topic->description) }}</textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('mentor.topics.show', $topic->id) }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection