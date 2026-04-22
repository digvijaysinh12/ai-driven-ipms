<header class="h-16 flex items-center justify-between px-8 bg-white border-b border-slate-200 sticky top-0 z-30">
    <div class="flex items-center gap-4">
        <!-- Search Bar (Optional) -->
        <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-slate-100 rounded-lg border border-slate-200 focus-within:ring-2 focus-within:ring-indigo-600/10 focus-within:border-indigo-600 transition-all w-64 group">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 group-focus-within:text-indigo-600"></i>
            <input type="text" placeholder="Quick search..." class="bg-transparent border-none text-xs focus:ring-0 w-full placeholder:text-slate-400">
        </div>
    </div>

    <div class="flex items-center gap-4">
        <!-- Notifications -->
        <button class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all relative group">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span class="absolute top-2 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white ring-4 ring-transparent group-hover:ring-indigo-100/50"></span>
        </button>

        <!-- Help -->
        <button class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-lg transition-all">
            <i data-lucide="help-circle" class="w-5 h-5"></i>
        </button>

        <div class="h-8 w-[1px] bg-slate-200 mx-2"></div>

        <!-- Date Display -->
        <div class="hidden lg:flex flex-col items-end mr-4">
            <p class="text-xs font-bold text-slate-900">{{ now()->format('l, jS F') }}</p>
            <p class="text-[10px] font-medium text-slate-400">Internship Tracker</p>
        </div>
    </div>
</header>
