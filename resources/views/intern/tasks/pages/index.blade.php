<x-app-layout>
    <div class="max-w-6xl mx-auto px-6 py-8 space-y-6">
        <div class="flex items-start justify-between gap-6">
            <div class="space-y-1">
                <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Intern Workspace</div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">My Tasks</h1>
                <p class="text-sm text-slate-500 font-medium">A calm workspace to plan, solve, and submit when you're ready.</p>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white border border-slate-100 shadow-sm text-xs font-bold text-slate-600">
                    <i data-lucide="sparkles" class="w-4 h-4 text-indigo-500"></i>
                    Self-paced tasks
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <div class="lg:col-span-5">
                <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Search</label>
                    <div class="relative">
                        <input
                            id="taskSearch"
                            type="text"
                            placeholder="Search by title or description…"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-200 outline-none text-sm"
                        />
                        <i data-lucide="search" class="absolute left-4 top-3.5 w-4 h-4 text-slate-400"></i>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                    <div class="space-y-1">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Filter</div>
                        <div class="text-sm font-bold text-slate-700">Status</div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" data-status="all" class="task-filter px-4 py-2 rounded-xl text-xs font-black bg-slate-900 text-white shadow-sm">All</button>
                        <button type="button" data-status="pending" class="task-filter px-4 py-2 rounded-xl text-xs font-black bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Pending</button>
                        <button type="button" data-status="completed" class="task-filter px-4 py-2 rounded-xl text-xs font-black bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Completed</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-100 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-slate-400"></i>
                    <div class="text-sm font-bold text-slate-800">Assigned</div>
                </div>
                <div id="taskListMeta" class="text-xs font-bold text-slate-400"></div>
            </div>
            <div id="taskList" class="divide-y divide-slate-100">
                @include('intern.tasks.partials.list')
            </div>
        </div>
    </div>

    <script>
        (function () {
            const listEl = document.getElementById('taskList');
            const searchEl = document.getElementById('taskSearch');
            const metaEl = document.getElementById('taskListMeta');
            const filterButtons = Array.from(document.querySelectorAll('.task-filter'));

            if (!listEl || !searchEl) return;

            let currentStatus = 'all';
            let searchDebounce = null;

            function setActiveFilter(status) {
                filterButtons.forEach(btn => {
                    const isActive = btn.getAttribute('data-status') === status;
                    btn.className = isActive
                        ? 'task-filter px-4 py-2 rounded-xl text-xs font-black bg-slate-900 text-white shadow-sm'
                        : 'task-filter px-4 py-2 rounded-xl text-xs font-black bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors';
                });
            }

            function buildUrl() {
                const params = new URLSearchParams();
                if (currentStatus !== 'all') params.set('status', currentStatus);
                const search = (searchEl.value || '').trim();
                if (search) params.set('search', search);
                const base = "{{ route('user.intern.tasks') }}";
                const query = params.toString();
                return query ? (base + '?' + query) : base;
            }

            async function loadTasks() {
                const url = buildUrl();
                if (metaEl) metaEl.textContent = 'Loading…';
                try {
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const html = await res.text();
                    listEl.innerHTML = html;
                    if (metaEl) metaEl.textContent = '';
                    if (window.lucide) window.lucide.createIcons();
                } catch (e) {
                    if (metaEl) metaEl.textContent = 'Failed';
                    listEl.innerHTML = '<div class="p-10 text-center text-sm text-slate-500">Failed to load tasks. Please refresh.</div>';
                }
            }

            filterButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    currentStatus = btn.getAttribute('data-status') || 'all';
                    setActiveFilter(currentStatus);
                    loadTasks();
                });
            });

            searchEl.addEventListener('input', () => {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(loadTasks, 250);
            });

            setActiveFilter(currentStatus);
        })();
    </script>
</x-app-layout>
