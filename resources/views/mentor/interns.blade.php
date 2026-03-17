@extends('layouts.mentor')
@section('title', 'Interns')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');
    .page-header {
        display: flex; justify-content: space-between;
        align-items: flex-end; padding-bottom: 20px;
        border-bottom: 1px solid #e5e5e5; margin-bottom: 24px;
    }
    .page-title { font-size: 20px; font-weight: 500; letter-spacing: -0.02em; }
    .page-meta  { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; margin-top: 4px; }

    .intern-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1px; background: #e5e5e5;
        border: 1px solid #e5e5e5; border-radius: 2px; overflow: hidden;
    }
    .intern-card {
        background: #fff; padding: 22px 22px 18px;
        display: flex; flex-direction: column; gap: 0;
        transition: background 0.12s;
    }
    .intern-card:hover { background: #fafafa; }
    .intern-avatar {
        width: 36px; height: 36px; border-radius: 2px;
        background: #1a1a1a; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-family: 'DM Mono', monospace; font-size: 14px; font-weight: 500;
        margin-bottom: 12px; letter-spacing: -0.02em;
    }
    .intern-name { font-size: 14px; font-weight: 500; color: #1a1a1a; margin-bottom: 2px; }
    .intern-email { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; margin-bottom: 14px; }
    .intern-meta {
        display: flex; align-items: center; justify-content: space-between;
        padding-top: 12px; border-top: 1px solid #f0f0f0;
    }
    .assigned-date { font-family: 'DM Mono', monospace; font-size: 10px; color: #bbb; }
    .btn-progress {
        font-family: 'DM Mono', monospace; font-size: 10px;
        color: #555; text-decoration: none;
        letter-spacing: 0.06em; text-transform: uppercase;
        border: 1px solid #e5e5e5; padding: 4px 10px; border-radius: 2px;
        transition: border-color 0.12s, color 0.12s;
    }
    .btn-progress:hover { border-color: #888; color: #1a1a1a; }
    .empty-card {
        background: #fff; border: 1px solid #e5e5e5; border-radius: 2px;
        padding: 56px; text-align: center;
        font-family: 'DM Mono', monospace; font-size: 13px; color: #aaa;
    }
</style>

<div class="page-header">
    <div>
        <div class="page-title">Interns</div>
        <div class="page-meta">{{ $interns->count() }} active</div>
    </div>
</div>

@if($interns->isEmpty())
    <div class="empty-card">No interns assigned to you yet. Ask HR to assign interns.</div>
@else
    <div class="intern-grid">
        @foreach($interns as $intern)
        <div class="intern-card">
            <div class="intern-avatar">{{ strtoupper(substr($intern->name, 0, 2)) }}</div>
            <div class="intern-name">{{ $intern->name }}</div>
            <div class="intern-email">{{ $intern->email }}</div>
            <div class="intern-meta">
                <div class="assigned-date">
                    Assigned {{ \Carbon\Carbon::parse($intern->assigned_at)->format('d M Y') }}
                </div>
                <a href="{{ route('mentor.interns.progress', $intern->id) }}" class="btn-progress">Progress →</a>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection