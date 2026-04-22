<!-- resources/views/intern/tasks/components/header.blade.php -->
<header class="h-[60px] bg-white border-b border-slate-200 px-5 flex items-center justify-between shrink-0">
    <div class="flex items-center gap-4">
        <a href="{{ route('user.intern.tasks') }}" class="flex items-center gap-1.5 text-slate-600 hover:text-slate-900 transition-colors">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
            <span class="text-sm font-medium">Back</span>
        </a>
        <div class="h-4 w-px bg-slate-200"></div>
        <h1 class="text-base font-bold text-slate-900" x-text="task.title">Task Title</h1>
    </div>

    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 rounded-lg text-slate-600 border border-slate-200">
            <i data-lucide="clock" class="w-4 h-4 text-slate-400"></i>
            <span class="text-sm font-mono font-medium" x-text="timer">00:00:00</span>
        </div>
        <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full border border-blue-100" x-text="submissionStatus">
            In Progress
        </span>
    </div>
</header>
