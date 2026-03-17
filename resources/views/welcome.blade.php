<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Internship Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f5f5f4;
            color: #1a1a1a;
        }

        /* ── Nav ── */
        .topnav {
            background: #fff;
            border-bottom: 1px solid #e5e5e5;
            padding: 0 40px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-brand {
            font-size: 13px;
            font-weight: 500;
            color: #1a1a1a;
            letter-spacing: -0.01em;
        }

        .nav-brand span {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            color: #aaa;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-left: 10px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link {
            font-size: 13px;
            color: #555;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 2px;
            transition: color 0.12s;
        }

        .nav-link:hover { color: #1a1a1a; }

        .nav-cta {
            font-size: 13px;
            font-weight: 500;
            color: #fff;
            background: #1a1a1a;
            text-decoration: none;
            padding: 7px 16px;
            border-radius: 2px;
            transition: background 0.12s;
        }

        .nav-cta:hover { background: #333; }

        /* ── Hero ── */
        .hero {
            max-width: 1100px;
            margin: 0 auto;
            padding: 80px 40px 72px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: start;
        }

        .hero-eyebrow {
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: 36px;
            font-weight: 500;
            letter-spacing: -0.03em;
            line-height: 1.18;
            color: #1a1a1a;
            margin-bottom: 20px;
        }

        .hero-desc {
            font-size: 14px;
            color: #666;
            line-height: 1.75;
            margin-bottom: 32px;
            font-weight: 300;
        }

        .hero-actions {
            display: flex;
            gap: 10px;
        }

        .btn-primary {
            background: #1a1a1a;
            color: #fff;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 2px;
            font-size: 13px;
            font-weight: 500;
            transition: background 0.12s;
        }

        .btn-primary:hover { background: #333; }

        .btn-ghost {
            background: #fff;
            color: #1a1a1a;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 2px;
            font-size: 13px;
            border: 1px solid #d4d4d4;
            transition: border-color 0.12s;
        }

        .btn-ghost:hover { border-color: #888; }

        /* ── Highlights panel ── */
        .highlights {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 2px;
            padding: 28px;
        }

        .highlights-label {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 20px;
        }

        .highlight-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
            color: #444;
            line-height: 1.5;
        }

        .highlight-item:last-child { border-bottom: none; }

        .highlight-item::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #1a1a1a;
            flex-shrink: 0;
            margin-top: 6px;
        }

        /* ── Roles section ── */
        .roles-section {
            background: #fff;
            border-top: 1px solid #e5e5e5;
            border-bottom: 1px solid #e5e5e5;
            padding: 64px 40px;
        }

        .roles-inner {
            max-width: 1100px;
            margin: 0 auto;
        }

        .section-header {
            margin-bottom: 40px;
        }

        .section-eyebrow {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 500;
            letter-spacing: -0.02em;
        }

        .roles-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            background: #e5e5e5;
            border: 1px solid #e5e5e5;
            border-radius: 2px;
            overflow: hidden;
        }

        .role-card {
            background: #fff;
            padding: 28px;
        }

        .role-name {
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-family: 'DM Mono', monospace;
            color: #1a1a1a;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #ebebeb;
        }

        .role-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13px;
            color: #555;
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .role-item::before {
            content: '—';
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            color: #ccc;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* ── Security section ── */
        .security-section {
            padding: 64px 40px;
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 60px;
            align-items: start;
        }

        .security-label {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 10px;
        }

        .security-title {
            font-size: 18px;
            font-weight: 500;
            letter-spacing: -0.02em;
            line-height: 1.4;
        }

        .security-desc {
            font-size: 13.5px;
            color: #555;
            line-height: 1.8;
            font-weight: 300;
        }

        .security-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 20px;
        }

        .security-tag {
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            color: #555;
            background: #f0f0f0;
            padding: 4px 10px;
            border-radius: 2px;
            letter-spacing: 0.04em;
        }

        /* ── Footer ── */
        footer {
            background: #fff;
            border-top: 1px solid #e5e5e5;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-brand {
            font-size: 12px;
            color: #aaa;
            font-family: 'DM Mono', monospace;
        }

        .footer-year {
            font-size: 12px;
            color: #ccc;
            font-family: 'DM Mono', monospace;
        }

        @media (max-width: 768px) {
            .hero, .security-section { grid-template-columns: 1fr; gap: 32px; }
            .roles-grid { grid-template-columns: 1fr; }
            .topnav { padding: 0 20px; }
            .hero { padding: 48px 20px; }
        }
    </style>
</head>
<body>

<!-- Nav -->
<header class="topnav">
    <div class="nav-brand">
        AI Internship Platform
        <span>Enterprise</span>
    </div>
    <nav class="nav-links">
        @auth
            <a href="{{ route(strtolower(auth()->user()->role->name) . '.dashboard') }}" class="nav-link">Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="nav-link">Sign In</a>
            <a href="{{ route('register') }}" class="nav-cta">Request Access</a>
        @endauth
    </nav>
</header>

<!-- Hero -->
<section class="hero">
    <div>
        <div class="hero-eyebrow">Internship Lifecycle Management</div>
        <h1 class="hero-title">Structured internship operations, end to end.</h1>
        <p class="hero-desc">
            A role-based enterprise platform for HR teams, team leads, and interns.
            Manages onboarding, approvals, mentor supervision, task tracking,
            and AI-powered evaluations in one controlled environment.
        </p>
        @guest
        <div class="hero-actions">
            <a href="{{ route('register') }}" class="btn-primary">Request Access</a>
            <a href="{{ route('login') }}" class="btn-ghost">Sign In</a>
        </div>
        @endguest
    </div>

    <div class="highlights">
        <div class="highlights-label">Platform Capabilities</div>
        <div class="highlight-item">Secure role-based access — HR, Mentor, Intern</div>
        <div class="highlight-item">HR approval workflow before system access is granted</div>
        <div class="highlight-item">Email verification and account status management</div>
        <div class="highlight-item">Technology-based intern and mentor assignment</div>
        <div class="highlight-item">AI-driven question generation and performance tracking</div>
        <div class="highlight-item">Controlled topic, submission and evaluation pipeline</div>
        <div class="highlight-item">Rate limiting and secure authentication architecture</div>
    </div>
</section>

<!-- Roles -->
<section class="roles-section">
    <div class="roles-inner">
        <div class="section-header">
            <div class="section-eyebrow">Access Levels</div>
            <div class="section-title">Role Capabilities</div>
        </div>

        <div class="roles-grid">
            <div class="role-card">
                <div class="role-name">HR</div>
                <div class="role-item">Approve or reject intern registrations</div>
                <div class="role-item">Assign technologies and mentors</div>
                <div class="role-item">Monitor system-wide activity</div>
                <div class="role-item">Manage roles and permissions</div>
            </div>
            <div class="role-card">
                <div class="role-name">Mentor / Team Lead</div>
                <div class="role-item">Create and publish internship topics</div>
                <div class="role-item">Review and evaluate submissions</div>
                <div class="role-item">Provide structured performance feedback</div>
                <div class="role-item">Track assigned intern progress</div>
            </div>
            <div class="role-card">
                <div class="role-name">Intern</div>
                <div class="role-item">Register with technology selection</div>
                <div class="role-item">Submit assigned tasks and assessments</div>
                <div class="role-item">View mentor feedback and evaluations</div>
                <div class="role-item">Track internship progress over time</div>
            </div>
        </div>
    </div>
</section>

<!-- Security -->
<div class="security-section">
    <div>
        <div class="security-label">Infrastructure</div>
        <div class="security-title">Enterprise Security Architecture</div>
    </div>
    <div>
        <p class="security-desc">
            The system enforces multi-layered security including authentication,
            status-based login restrictions, role-based middleware authorization,
            resource-level policies, and rate limiting to prevent abuse.
            Designed to meet enterprise-level access control standards.
        </p>
        <div class="security-tags">
            <span class="security-tag">Role Middleware</span>
            <span class="security-tag">Status Gating</span>
            <span class="security-tag">Rate Limiting</span>
            <span class="security-tag">Email Verification</span>
            <span class="security-tag">Resource Policies</span>
            <span class="security-tag">CSRF Protection</span>
        </div>
    </div>
</div>

<footer>
    <span class="footer-brand">AI Internship Platform</span>
    <span class="footer-year">© {{ date('Y') }}</span>
</footer>

</body>
</html>