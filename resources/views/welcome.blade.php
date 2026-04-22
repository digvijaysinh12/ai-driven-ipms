<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI-IPMS | Advanced Internship Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="antialiased bg-slate-50 font-sans text-slate-900">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-slate-900 rounded-lg flex items-center justify-center">
                        <i data-lucide="brain-circuit" class="text-white w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tight">AI-IPMS</span>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary text-xs uppercase tracking-widest px-6 py-2.5">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">Sign in</a>
                        <a href="{{ route('register') }}" class="btn btn-accent text-xs uppercase tracking-widest px-6 py-2.5 rounded-xl">
                            Request Access
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main>
        <div class="relative overflow-hidden pt-16 pb-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-center">
                    <div class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left">
                        <p class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4">Lifecycle Management</p>
                        <h1 class="text-5xl font-extrabold tracking-tight text-slate-900 sm:text-6xl mb-6 leading-[1.1]">
                            Structured <span class="text-indigo-600">Internship</span> Operations.
                        </h1>
                        <p class="text-lg text-slate-500 font-medium leading-relaxed mb-10">
                            A role-based enterprise platform for HR teams, team leads, and interns. 
                            Manage onboarding, approvals, mentorship, and AI-powered evaluations in one controlled environment.
                        </p>
                        <div class="flex flex-wrap gap-4 sm:justify-center lg:justify-start">
                            <a href="{{ route('register') }}" class="btn btn-primary px-8 py-4 rounded-2xl text-base shadow-2xl shadow-slate-900/20 active:scale-95 transition-all">
                                Get Started
                            </a>
                            <div class="flex -space-x-2">
                                @foreach([1,2,3,4] as $i)
                                    <div class="w-10 h-10 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-[10px] font-bold overflow-hidden shadow-sm">
                                        <img src="https://i.pravatar.cc/100?img={{ $i+20 }}" alt="User">
                                    </div>
                                @endforeach
                                <div class="w-10 h-10 rounded-full border-2 border-white bg-indigo-50 flex items-center justify-center text-[10px] font-bold text-indigo-600 shadow-sm">
                                    +500
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-16 lg:mt-0 lg:col-span-6">
                        <div class="relative mx-auto w-full rounded-3xl shadow-2xl overflow-hidden border border-slate-200 aspect-video bg-white p-4">
                            <div class="bg-slate-50 rounded-2xl w-full h-full flex flex-col items-center justify-center gap-4 border border-slate-100">
                                <i data-lucide="layout" class="w-20 h-20 text-slate-200"></i>
                                <p class="text-slate-400 font-medium text-sm">Dashboard Preview</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Access -->
        <div class="bg-white py-24 border-y border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">Access Levels</h2>
                    <p class="mt-4 text-slate-500 font-medium">Fine-grained control for every stakeholder</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach([
                        ['role' => 'HR', 'icon' => 'user-check', 'color' => 'bg-emerald-50 text-emerald-600', 'items' => ['Approve registrations', 'Assign mentors', 'Monitor progress', 'Report Generation']],
                        ['role' => 'Mentor', 'icon' => 'users', 'color' => 'bg-indigo-50 text-indigo-600', 'items' => ['Create curriculums', 'Review submissions', 'AI evaluation review', 'Direct supervision']],
                        ['role' => 'Intern', 'icon' => 'graduation-cap', 'color' => 'bg-amber-50 text-amber-600', 'items' => ['Hands-on tasks', 'Real-time AI feedback', 'Attendance tracking', 'Performance metrics']]
                    ] as $item)
                        <div class="card p-8 group hover:border-indigo-200 transition-all hover:shadow-xl hover:shadow-indigo-500/5">
                            <div class="w-12 h-12 {{ $item['color'] }} rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <i data-lucide="{{ $item['icon'] }}" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-4">{{ $item['role'] }}</h3>
                            <ul class="space-y-3">
                                @foreach($item['items'] as $li)
                                    <li class="flex items-center gap-2 text-sm text-slate-600 font-medium">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                                        {{ $li }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>

    <footer class="py-12 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <span class="text-sm font-bold text-slate-400 font-mono tracking-tighter uppercase">AI-IPMS Platform</span>
            <div class="flex gap-8">
                <a href="#" class="text-slate-400 hover:text-slate-900 text-sm font-medium">Documentation</a>
                <a href="#" class="text-slate-400 hover:text-slate-900 text-sm font-medium">Privacy</a>
                <a href="#" class="text-slate-400 hover:text-slate-900 text-sm font-medium">Terms</a>
            </div>
            <p class="text-xs text-slate-400 font-medium">© {{ date('Y') }} AI-Driven Internship System</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>