@if(session('success'))
    <div class="fixed top-5 right-5 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-[999]">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="fixed top-5 right-5 bg-red-500 text-white px-6 py-3 rounded shadow-lg z-[999]">
        {{ session('error') }}
    </div>
@endif
<div id="assignModal" 
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-fadeIn">

    <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl relative overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- MODAL HEADER -->
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                    <i data-lucide="send" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Deployment</h2>
                    <h3 class="text-lg font-black text-slate-900">Assign Challenge</h3>
                </div>
            </div>
            
            <button type="button" onclick="closeAssignModal()" class="w-8 h-8 hover:bg-slate-100 rounded-lg flex items-center justify-center">
                ✕
            </button>
        </div>

        <!-- ✅ FORM FIXED -->
        <form id="assignForm" 
              method="POST" 
              action="{{ route('user.mentor.tasks.assign', $task) }}"
              class="p-8 flex-1 overflow-y-auto space-y-6">
            @csrf

            <!-- INTERN SELECT -->
            <div>
                <label class="text-xs font-bold text-slate-500">Select Interns</label>

                <select name="intern_ids[]" multiple
                        class="w-full border p-3 rounded mt-2 min-h-[120px]">
                    @foreach($interns as $intern)
                        <option value="{{ $intern->id }}">
                            {{ $intern->name }} ({{ $intern->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- DUE DATE -->
            <div>
                <label class="text-xs font-bold text-slate-500">Due Date</label>
                <input type="date" name="due_at" class="w-full border p-3 rounded mt-2">
            </div>

        </form>

        <!-- FOOTER -->
        <div class="px-8 py-6 bg-slate-50 border-t flex justify-end gap-3">
            <button type="button" onclick="closeAssignModal()" class="px-4 py-2 border rounded">
                Cancel
            </button>

            <!-- ✅ SUBMIT BUTTON -->
            <button type="submit" form="assignForm" 
                    class="bg-slate-900 text-white px-6 py-2 rounded">
                Assign Task
            </button>
        </div>

    </div>
</div>