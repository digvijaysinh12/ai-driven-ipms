<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | AI Internship Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f5f5f4;
            color: #1a1a1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .auth-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 900px;
            background: #fff;
            border: 1px solid #e2e2e2;
            border-radius: 2px;
        }

        .auth-panel { padding: 52px; }

        .auth-brand {
            padding: 52px;
            background: #1a1a1a;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-label {
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #888;
        }

        .brand-name {
            font-size: 22px;
            font-weight: 500;
            color: #fff;
            margin-top: 8px;
            letter-spacing: -0.02em;
            line-height: 1.4;
        }

        .brand-tagline { font-size: 13px; color: #555; line-height: 1.7; font-weight: 300; }

        .auth-title {
            font-size: 20px;
            font-weight: 500;
            letter-spacing: -0.02em;
            margin-bottom: 28px;
        }

        .form-group { margin-bottom: 14px; }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 6px;
            font-family: 'DM Mono', monospace;
        }

        .form-input, .form-select {
            width: 100%;
            border: 1px solid #d4d4d4;
            border-radius: 2px;
            padding: 10px 12px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: #1a1a1a;
            background: #fafafa;
            transition: border-color 0.15s, background 0.15s;
            outline: none;
            appearance: none;
        }

        .form-input:focus, .form-select:focus {
            border-color: #1a1a1a;
            background: #fff;
        }

        .select-wrapper { position: relative; }
        .select-wrapper::after {
            content: '↓';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            color: #888;
            pointer-events: none;
        }

        .btn-primary {
            width: 100%;
            background: #1a1a1a;
            color: #fff;
            border: none;
            border-radius: 2px;
            padding: 12px;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.04em;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            margin-top: 8px;
            transition: background 0.15s;
        }

        .btn-primary:hover { background: #333; }

        .divider { height: 1px; background: #ebebeb; margin: 18px 0; }

        .auth-footer { font-size: 13px; color: #777; text-align: center; }

        .link { color: #1a1a1a; text-decoration: underline; text-underline-offset: 2px; }

        #technology-wrapper { display: none; }

        @media (max-width: 640px) {
            .auth-wrapper { grid-template-columns: 1fr; }
            .auth-brand { display: none; }
            .auth-panel { padding: 36px 24px; }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">

    <div class="auth-brand">
        <div>
            <div class="brand-label">AI Internship Platform</div>
            <div class="brand-name">Manage.<br>Assess.<br>Grow.</div>
        </div>
        <div class="brand-tagline">
            Structured learning management<br>for teams and their interns.
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-title">Create Account</div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="form-input" placeholder="Jane Smith" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="form-input" placeholder="you@company.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password"
                       class="form-input" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation"
                       class="form-input" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label class="form-label">Role</label>
                <div class="select-wrapper">
                    <select name="role" id="role" class="form-select" required>
                        <option value="">Select role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ $role->name === 'mentor' ? 'Team Lead' : ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group" id="technology-wrapper">
                <label class="form-label">Technology</label>
                <div class="select-wrapper">
                    <select name="technology_id" class="form-select">
                        <option value="">Select technology</option>
                        @foreach ($technologies as $t)
                            <option value="{{ $t->id }}" {{ old('technology_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-primary">Create Account</button>

            <div class="divider"></div>
            <div class="auth-footer">
                Already registered? <a href="{{ route('login') }}" class="link">Sign in</a>
            </div>
        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });

document.addEventListener('DOMContentLoaded', function(){
    const roleSelect = document.getElementById('role');
    const techWrapper = document.getElementById('technology-wrapper');
    function toggleTechnology(){
        techWrapper.style.display = roleSelect.value === 'intern' ? 'block' : 'none';
    }
    toggleTechnology();
    roleSelect.addEventListener('change', toggleTechnology);
});
</script>
@if(session('success'))<script>Toast.fire({ icon: 'success', title: "{{ session('success') }}" });</script>@endif
@if(session('error'))<script>Toast.fire({ icon: 'error', title: "{{ session('error') }}" });</script>@endif
@if($errors->any())<script>Toast.fire({ icon: 'error', title: "{{ $errors->first() }}" });</script>@endif

</body>
</html>