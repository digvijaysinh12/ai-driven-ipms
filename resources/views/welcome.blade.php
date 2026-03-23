<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Internship Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
</head>
<body style="background:#f5f5f4;color:#1a1a1a;font-family:'DM Sans',sans-serif;">

{{-- ── Nav ── --}}
<header style="background:#fff;border-bottom:1px solid #e5e5e5;padding:0 40px;
               height:52px;display:flex;align-items:center;justify-content:space-between;
               position:sticky;top:0;z-index:100;">
    <div style="display:flex;align-items:center;gap:10px;">
        <span style="font-size:13px;font-weight:500;letter-spacing:-0.01em;">
            AI Internship Platform
        </span>
        <span style="font-family:'DM Mono',monospace;font-size:10px;color:#aaa;
                     letter-spacing:0.08em;text-transform:uppercase;">
            Enterprise
        </span>
    </div>
    <nav style="display:flex;align-items:center;gap:8px;">
        @auth
            <a href="{{ route(strtolower(auth()->user()->role->name) . '.dashboard') }}"
               class="btn-primary btn-sm">
                Dashboard →
            </a>
        @else
            <a href="{{ route('login') }}"
               style="font-size:13px;color:#555;text-decoration:none;padding:6px 12px;">
                Sign In
            </a>
            <a href="{{ route('register') }}" class="btn-primary btn-sm">
                Request Access
            </a>
        @endauth
    </nav>
</header>

{{-- ── Hero ── --}}
<section style="max-width:1100px;margin:0 auto;padding:80px 40px 72px;
                display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:start;">
    <div>
        <div style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:0.1em;
                    text-transform:uppercase;color:#888;margin-bottom:20px;">
            Internship Lifecycle Management
        </div>

        <h1 style="font-size:36px;font-weight:500;letter-spacing:-0.03em;
                   line-height:1.18;color:#1a1a1a;margin-bottom:20px;">
            Structured internship<br>operations, end to end.
        </h1>

        <p style="font-size:14px;color:#666;line-height:1.75;
                  margin-bottom:32px;font-weight:300;">
            A role-based enterprise platform for HR teams, team leads, and interns.
            Manages onboarding, approvals, mentor supervision, task tracking,
            and AI-powered evaluations in one controlled environment.
        </p>

        @guest
            <div style="display:flex;gap:10px;">
                <a href="{{ route('register') }}" class="btn-primary">Request Access</a>
                <a href="{{ route('login') }}"    class="btn-outline">Sign In</a>
            </div>
        @endguest
    </div>

    {{-- Highlights panel --}}
    <div style="background:#fff;border:1px solid #e5e5e5;border-radius:2px;padding:28px;">
        <div class="section-label" style="margin-bottom:20px;">Platform Capabilities</div>

        @foreach([
            'Secure role-based access — HR, Mentor, Intern',
            'HR approval workflow before system access is granted',
            'AI-driven question generation and code evaluation',
            'Technology-based intern and mentor assignment',
            'Attendance tracking via office WiFi IP detection',
            'Controlled topic, submission and evaluation pipeline',
            'Digital certificates with unique verification codes',
        ] as $item)
            <div style="display:flex;align-items:flex-start;gap:12px;
                        padding:10px 0;border-bottom:1px solid #f0f0f0;
                        font-size:13px;color:#444;line-height:1.5;">
                <span style="width:5px;height:5px;border-radius:50%;background:#1a1a1a;
                             flex-shrink:0;margin-top:6px;"></span>
                {{ $item }}
            </div>
        @endforeach
    </div>
</section>

{{-- ── Role capabilities ── --}}
<section style="background:#fff;border-top:1px solid #e5e5e5;
                border-bottom:1px solid #e5e5e5;padding:64px 40px;">
    <div style="max-width:1100px;margin:0 auto;">

        <div class="section-label" style="margin-bottom:10px;">Access Levels</div>
        <div style="font-size:22px;font-weight:500;letter-spacing:-0.02em;margin-bottom:40px;">
            Role Capabilities
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1px;
                    background:#e5e5e5;border:1px solid #e5e5e5;
                    border-radius:2px;overflow:hidden;">

            @foreach([
                'HR' => [
                    'Approve or reject intern registrations',
                    'Assign technologies and mentors to interns',
                    'Monitor all intern progress and activity',
                    'Generate internship performance reports',
                ],
                'Mentor / Team Lead' => [
                    'Create and publish internship topics',
                    'Generate AI questions and review them',
                    'Evaluate intern submissions and override scores',
                    'Track assigned intern progress in detail',
                ],
                'Intern' => [
                    'Register with technology selection',
                    'Solve assigned exercises in built-in editor',
                    'View AI and mentor feedback on submissions',
                    'Track attendance, scores, and final grade',
                ],
            ] as $role => $items)
                <div style="background:#fff;padding:28px;">
                    <div style="font-family:'DM Mono',monospace;font-size:12px;font-weight:500;
                                letter-spacing:0.08em;text-transform:uppercase;color:#1a1a1a;
                                margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #ebebeb;">
                        {{ $role }}
                    </div>
                    @foreach($items as $item)
                        <div style="display:flex;align-items:flex-start;gap:10px;
                                    font-size:13px;color:#555;margin-bottom:10px;line-height:1.5;">
                            <span style="font-family:'DM Mono',monospace;font-size:11px;
                                         color:#ccc;flex-shrink:0;margin-top:1px;">—</span>
                            {{ $item }}
                        </div>
                    @endforeach
                </div>
            @endforeach

        </div>
    </div>
</section>

{{-- ── Security section ── --}}
<section style="max-width:1100px;margin:0 auto;padding:64px 40px;
                display:grid;grid-template-columns:1fr 2fr;gap:60px;align-items:start;">
    <div>
        <div class="section-label" style="margin-bottom:10px;">Infrastructure</div>
        <div style="font-size:18px;font-weight:500;letter-spacing:-0.02em;line-height:1.4;">
            Enterprise Security Architecture
        </div>
    </div>
    <div>
        <p style="font-size:13.5px;color:#555;line-height:1.8;font-weight:300;margin-bottom:20px;">
            The system enforces multi-layered security including authentication,
            status-based login restrictions, role-based middleware authorization,
            resource-level policies, and rate limiting to prevent abuse.
            Designed to meet enterprise-level access control standards.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
            @foreach(['Role Middleware','Status Gating','Rate Limiting',
                      'Email Verification','Resource Policies','CSRF Protection'] as $tag)
                <span style="font-family:'DM Mono',monospace;font-size:11px;color:#555;
                             background:#f0f0f0;padding:4px 10px;border-radius:2px;
                             letter-spacing:0.04em;">
                    {{ $tag }}
                </span>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Footer ── --}}
<footer style="background:#fff;border-top:1px solid #e5e5e5;padding:20px 40px;
               display:flex;justify-content:space-between;align-items:center;">
    <span style="font-size:12px;color:#aaa;font-family:'DM Mono',monospace;">
        AI Internship Platform
    </span>
    <span style="font-size:12px;color:#ccc;font-family:'DM Mono',monospace;">
        © {{ date('Y') }}
    </span>
</footer>

{{-- Responsive --}}
<style>
    @media (max-width: 768px) {
        section { grid-template-columns: 1fr !important; gap: 32px !important; }
        header  { padding: 0 20px !important; }
    }
</style>

</body>
</html>