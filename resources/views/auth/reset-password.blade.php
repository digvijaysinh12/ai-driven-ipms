@extends('layouts.guest')
@section('title', 'Set New Password')

@section('content')
<div class="auth-body" style="justify-content:center;">
    <div style="width:100%;max-width:420px;background:#fff;border:1px solid #e2e2e2;border-radius:2px;padding:48px 44px;">
        <a href="{{ route('login') }}" class="back-link" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:32px;">&larr; Back to Sign In</a>

        <div class="auth-title" style="margin-bottom:8px;">Create New Password</div>
        <p style="font-size:13px;color:#666;margin-bottom:28px;line-height:1.6;">
            Choose a new password for your account and confirm it below.
        </p>

        @if(session('status'))
            <div class="flash flash-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    class="form-input"
                    placeholder="you@company.com"
                    required
                    autofocus
                    autocomplete="username"
                >
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">New Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-input"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                >
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input
                    type="password"
                    name="password_confirmation"
                    class="form-input"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                >
                @error('password_confirmation') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn-primary" style="width:100%;margin-top:8px;">Reset Password</button>
        </form>
    </div>
</div>
@endsection
