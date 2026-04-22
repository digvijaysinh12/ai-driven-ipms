<x-app-layout>
    <div class="space-y-8 pb-12">
        
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-black text-indigo-500 uppercase tracking-widest">Management</span>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Task Dashboard</h1>
                <p class="text-slate-500 mt-1 font-medium">Curate and launch specialized challenges for your interns.</p>
            </div>

            <a href="{{ route('user.mentor.tasks.create') }}"
               class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all flex items-center gap-2 active:scale-95">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Create New Task
            </a>
        </div>

        <!-- STATS GRID -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $statItems = [
                    ['label' => 'Total Tasks', 'value' => $stats['total'], 'color' => 'slate', 'icon' => 'layers'],
                    ['label' => 'Drafts', 'value' => $stats['draft'], 'color' => 'amber', 'icon' => 'edit-3'],
                    ['label' => 'Ready', 'value' => $stats['ready'], 'color' => 'blue', 'icon' => 'check-circle'],
                    ['label' => 'Assigned', 'value' => $stats['assigned'], 'color' => 'emerald', 'icon' => 'send'],
                ];
            @endphp

            @foreach($statItems as $item)
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-{{ $item['color'] }}-200 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-{{ $item['color'] }}-50 rounded-xl flex items-center justify-center text-{{ $item['color'] }}-600">
                            <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $item['label'] }}</span>
                    </div>
                    <h2 class="text-3xl font-black text-slate-900">{{ number_format($item['value']) }}</h2>
                </div>
            @endforeach
        </div>

        <!-- SEARCH + FILTER BAR -->
        <div class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" id="search"
                       placeholder="Search by title, technology, or category..."
                       class="w-full bg-white border border-slate-200 pl-11 pr-4 py-3 rounded-2xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 transition-all outline-none">
            </div>

            <div class="relative min-w-[200px]">
                <i data-lucide="filter" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                <select id="status"
                        class="w-full bg-white border border-slate-200 pl-11 pr-4 py-3 rounded-2xl text-sm font-bold text-slate-700 appearance-none focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="draft">Drafts</option>
                    <option value="ready">Ready to Assign</option>
                    <option value="assigned">Currently Assigned</option>
                </select>
            </div>
        </div>

        <!-- TABLE AREA -->
        <div id="taskTable" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden min-h-[400px]">
            @include('mentor.tasks.partials.table', ['tasks' => $tasks])
        </div>

    </div>

    <!-- SCRIPTS -->
    <script>
    function loadTasks(url = "{{ route('user.mentor.tasks.index') }}") {
        let search = $('#search').val();
        let status = $('#status').val();

        $.ajax({
            url: url,
            type: "GET",
            data: { search: search, status: status },
            beforeSend: function () {
                $('#taskTable').addClass('opacity-50 pointer-events-none');
            },
            success: function (data) {
                $('#taskTable').html(data).removeClass('opacity-50 pointer-events-none');
                if(window.lucide) { window.lucide.createIcons(); }
            }
        });
    }

    // Event Listeners
    $('#search').on('input', debounce(function() { loadTasks(); }, 300));
    $('#status').on('change', function () { loadTasks(); });

    $(document).on('click', '#taskTable .pagination a', function (e) {
        e.preventDefault();
        loadTasks($(this).attr('href'));
    });

    // Simple Debounce
    function debounce(func, wait) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), wait);
        };
    }
    </script>
</x-app-layout>