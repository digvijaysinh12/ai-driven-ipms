@extends('layouts.hr')

@section('title', 'Intern Progress')

@section('content')

<style>
    .page-header {
        padding-bottom: 18px;
        border-bottom: 1px solid #e5e5e5;
        margin-bottom: 24px;
    }

    .page-title { font-size: 18px; font-weight: 500; letter-spacing: -0.01em; }

    .page-meta {
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        color: #aaa;
        margin-top: 3px;
    }

    .info-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        padding: 28px;
    }

    .info-text {
        font-size: 13.5px;
        color: #555;
        line-height: 1.7;
    }
</style>

<div class="page-header">
    <div class="page-title">Intern Progress</div>
    <div class="page-meta">Monitor performance</div>
</div>

<div class="info-card">
    <div class="info-text">
        Monitor intern assignments, submissions, and mentor feedback here.
        Select an intern from the Intern–Mentor Mapping table to view their individual progress.
    </div>
</div>

@endsection