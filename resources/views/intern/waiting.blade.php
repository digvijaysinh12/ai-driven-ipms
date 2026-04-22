@extends('layouts.app')

@section('title', 'Waiting for Assignment')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

    .waiting-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        padding: 48px;
        max-width: 520px;
    }

    .waiting-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #aaa;
        margin-bottom: 14px;
    }

    .waiting-title {
        font-size: 18px;
        font-weight: 500;
        letter-spacing: -0.01em;
        margin-bottom: 12px;
    }

    .waiting-desc {
        font-size: 13.5px;
        color: #666;
        line-height: 1.7;
        font-weight: 300;
    }

    .waiting-steps {
        margin-top: 28px;
        border-top: 1px solid #ebebeb;
        padding-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .step {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 13px;
        color: #555;
    }

    .step-num {
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        color: #aaa;
        min-width: 18px;
    }

    .step.done .step-num { color: #1a1a1a; }
    .step.done { color: #1a1a1a; }
</style>

<div class="waiting-card">
    <div class="waiting-label">Account Status</div>
    <div class="waiting-title">Pending Mentor Assignment</div>
    <div class="waiting-desc">
        Your account has been approved by HR. You will gain access to your topics and tasks
        once a mentor (team lead) is assigned to you.
    </div>

    <div class="waiting-steps">
        <div class="step done">
            <span class="step-num">01</span>
            <span>Registration submitted</span>
        </div>
        <div class="step done">
            <span class="step-num">02</span>
            <span>Account approved by HR</span>
        </div>
        <div class="step">
            <span class="step-num">03</span>
            <span>Mentor assignment â€” pending</span>
        </div>
        <div class="step">
            <span class="step-num">04</span>
            <span>Topic assigned and work begins</span>
        </div>
    </div>
</div>

@endsection
