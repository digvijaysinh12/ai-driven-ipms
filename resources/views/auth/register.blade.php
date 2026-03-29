@extends('layouts.guest')
@section('title', 'Create Account')

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
        <div class="auth-title">Create Account</div>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="form-input" placeholder="Jane Smith" required>
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="form-input" placeholder="you@company.com" required>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-input" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label class="form-label">Role</label>
                <div style="position:relative;">
                    <select name="role" id="role" class="form-select" required>
                        <option value="">Select role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ $role->name === 'mentor' ? 'Team Lead' : ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('role') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group" id="technology-wrapper" style="display:none;">
                <label class="form-label">Technology</label>
                <select name="technology_id" class="form-select">
                    <option value="">Select technology</option>
                    @foreach ($technologies as $t)
                        <option value="{{ $t->id }}" {{ old('technology_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
                @error('technology_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn-primary" style="width:100%;margin-top:8px;">Create Account</button>

            <div class="divider"></div>
            <div class="auth-footer">
                Already registered? <a href="{{ route('login') }}" class="link">Sign in</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect  = document.getElementById('role');
    const techWrapper = document.getElementById('technology-wrapper');
    function toggle() {
        techWrapper.style.display = roleSelect.value === 'intern' ? 'block' : 'none';
    }
    toggle();
    roleSelect.addEventListener('change', toggle);
});
</script>
@endsection