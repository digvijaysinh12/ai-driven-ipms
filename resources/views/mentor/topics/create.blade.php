@extends('layouts.app')
@section('title', 'New Topic')

@section('content')
<div class="page-header">
    <div class="page-title">New Topic</div>
    <a href="{{ route('user.mentor.tasks.index') }}" class="back-link">← All Topics</a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('user.mentor.tasks.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" value="{{ old('title') }}"
                   class="form-input" placeholder="e.g. PHP Arrays & Functions" required>
            @error('title') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-textarea"
                      placeholder="Brief description of what this topic covers...">{{ old('description') }}</textarea>
        </div>

        <div class="form-group" style="margin-top:28px;">
            <div style="font-family:'DM Mono',monospace;font-size:11px;font-weight:500;letter-spacing:0.08em;text-transform:uppercase;color:#888;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #ebebeb;">
                Question Counts
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                @foreach(['mcq' => 'MCQ', 'blank' => 'Fill in Blank', 'true_false' => 'True / False', 'output' => 'Output', 'coding' => 'Coding'] as $key => $label)
                    <div>
                        <label class="form-label">{{ $label }}</label>
                        <input type="number" name="{{ $key }}_count"
                               value="{{ old($key.'_count', 0) }}"
                               min="0" class="form-input"
                               style="font-family:'DM Mono',monospace;">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Create Topic</button>
            <a href="{{ route('user.mentor.tasks.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
