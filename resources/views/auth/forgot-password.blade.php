<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | AI Internship Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f5f5f4;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border: 1px solid #e2e2e2;
            border-radius: 2px;
            padding: 48px 44px;
        }

        .back-link {
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #888;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 32px;
        }

        .back-link:hover { color: #1a1a1a; }

        .card-title {
            font-size: 20px;
            font-weight: 500;
            letter-spacing: -0.02em;
            margin-bottom: 8px;
        }

        .card-desc {
            font-size: 13px;
            color: #666;
            margin-bottom: 28px;
            line-height: 1.6;
        }

        .status-msg {
            font-size: 13px;
            background: #f0faf0;
            border: 1px solid #c6e6c6;
            color: #2d6a2d;
            padding: 10px 14px;
            border-radius: 2px;
            margin-bottom: 20px;
        }

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

        .form-input {
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
            margin-bottom: 4px;
        }

        .form-input:focus { border-color: #1a1a1a; background: #fff; }

        .error-text { font-size: 12px; color: #b91c1c; margin-bottom: 16px; }

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
            margin-top: 16px;
            transition: background 0.15s;
        }

        .btn-primary:hover { background: #333; }
    </style>
</head>
<body>

<div class="card">
    <a href="{{ route('login') }}" class="back-link">← Back to Sign In</a>

    <div class="card-title">Reset Password</div>
    <div class="card-desc">Enter your email and we'll send you a reset link.</div>

    @if (session('status'))
        <div class="status-msg">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <label class="form-label">Email Address</label>
        <input type="email" name="email" value="{{ old('email') }}"
               class="form-input" placeholder="you@company.com" required>

        @error('email') <div class="error-text">{{ $message }}</div> @enderror

        <button type="submit" class="btn-primary">Send Reset Link</button>
    </form>
</div>

</body>
</html>