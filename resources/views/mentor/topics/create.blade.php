@extends('layouts.mentor')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e5e5;
        margin-bottom: 28px;
    }

    .page-title {
        font-size: 18px;
        font-weight: 500;
        letter-spacing: -0.01em;
        font-family: 'DM Sans', sans-serif;
    }

    .back-link {
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #888;
        text-decoration: none;
    }

    .back-link:hover { color: #1a1a1a; }

    .form-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        padding: 32px;
        max-width: 680px;
    }

    .form-group { margin-bottom: 20px; }

    .form-label {
        display: block;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #666;
        margin-bottom: 7px;
        font-family: 'DM Mono', monospace;
    }

    .form-input, .form-textarea {
        width: 100%;
        border: 1px solid #d4d4d4;
        border-radius: 2px;
        padding: 10px 12px;
        font-size: 14px;
        font-family: 'DM Sans', sans-serif;
        color: #1a1a1a;
        background: #fafafa;
        outline: none;
        transition: border-color 0.15s, background 0.15s;
    }

    .form-input:focus, .form-textarea:focus {
        border-color: #1a1a1a;
        background: #fff;
    }

    .form-textarea {
        resize: vertical;
        min-height: 90px;
    }

    .section-label {
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #888;
        font-family: 'DM Mono', monospace;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ebebeb;
    }

    .counts-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .count-field label {
        display: block;
        font-size: 11px;
        color: #888;
        margin-bottom: 5px;
        font-family: 'DM Mono', monospace;
        letter-spacing: 0.05em;
    }

    .count-field input {
        width: 100%;
        border: 1px solid #d4d4d4;
        border-radius: 2px;
        padding: 9px 12px;
        font-size: 14px;
        font-family: 'DM Mono', monospace;
        color: #1a1a1a;
        background: #fafafa;
        outline: none;
        transition: border-color 0.15s;
    }

    .count-field input:focus { border-color: #1a1a1a; background: #fff; }

    .form-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid #ebebeb;
    }

    .btn-primary {
        background: #1a1a1a;
        color: #fff;
        border: none;
        border-radius: 2px;
        padding: 10px 24px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        transition: background 0.15s;
    }

    .btn-primary:hover { background: #333; }

    .btn-ghost {
        background: none;
        color: #888;
        border: 1px solid #d4d4d4;
        border-radius: 2px;
        padding: 10px 20px;
        font-size: 13px;
        text-decoration: none;
        font-family: 'DM Sans', sans-serif;
        transition: border-color 0.15s, color 0.15s;
    }

    .btn-ghost:hover { border-color: #888; color: #1a1a1a; }
</style>

<div class="page-header">
    <div class="page-title">New Topic</div>
    <a href="{{ route('mentor.topics.index') }}" class="back-link">← All Topics</a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('mentor.topics.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-input" placeholder="e.g. PHP Arrays & Functions" required>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-textarea" placeholder="Brief description of what this topic covers..."></textarea>
        </div>

        <div class="form-group" style="margin-top: 28px;">
            <div class="section-label">Question Counts</div>
            <div class="counts-grid">
                <div class="count-field">
                    <label>MCQ</label>
                    <input type="number" name="mcq_count" min="0" placeholder="0">
                </div>
                <div class="count-field">
                    <label>Fill in Blank</label>
                    <input type="number" name="blank_count" min="0" placeholder="0">
                </div>
                <div class="count-field">
                    <label>True / False</label>
                    <input type="number" name="true_false_count" min="0" placeholder="0">
                </div>
                <div class="count-field">
                    <label>Output</label>
                    <input type="number" name="output_count" min="0" placeholder="0">
                </div>
                <div class="count-field">
                    <label>Coding</label>
                    <input type="number" name="coding_count" min="0" placeholder="0">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Create Topic</button>
            <a href="{{ route('mentor.topics.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>

@endsection