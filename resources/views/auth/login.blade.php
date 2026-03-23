@extends('layouts.guest')
@section('title', 'Sign In')

@section('content')
<div class="auth-wrapper">
    <div class="auth-brand">
        <div>
            <div class="brand-label">AI Internship Platform</div>
            <div class="brand-name">Manage.<br>Assess.<br>Grow.</div>
        </div>
        <div class="brand-tagline">Structured learning management<br>for teams and their interns.</div>
    </div>

    <div class="auth-panel">
        <div class="auth-title">Sign In</div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="form-input" placeholder="you@company.com" required autofocus>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password"
                       class="form-input" placeholder="••••••••" required>
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;margin-top:4px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#555;cursor:pointer;">
                    <input type="checkbox" name="remember" style="accent-color:#1a1a1a;">
                    Remember me
                </label>
                <a href="{{ route('password.request') }}" class="link" style="font-size:13px;">Forgot password?</a>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;">Sign In</button>

            <div class="divider"></div>
            <div class="auth-footer">
                No account? <a href="{{ route('register') }}" class="link">Create one</a>
            </div>
        </form>
    </div>
</div>
@endsection