@if (session('success') || session('error') || session('status'))
    <div id="toast-container" 
         class="fixed bottom-10 right-10 z-[100] flex flex-col gap-3 pointer-events-none transform transition-all duration-500 ease-out translate-y-0 opacity-100"
         x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)">
        
        @php
            $type = session('success') ? 'success' : (session('error') ? 'error' : 'info');
            $message = session('success') ?? session('error') ?? session('status');
            $icon = $type === 'success' ? 'check-circle' : ($type === 'error' ? 'alert-triangle' : 'info');
            $color = $type === 'success' ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : ($type === 'error' ? 'text-red-600 bg-red-50 border-red-100' : 'text-blue-600 bg-blue-50 border-blue-100');
        @endphp

        <div class="pointer-events-auto flex items-center gap-3 px-6 py-4 rounded-2xl bg-white border border-slate-100 shadow-[0_20px_50px_rgba(0,0,0,0.1)] min-w-[320px] anima-slide-up">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center p-2 {{ $color }}">
                <i data-lucide="{{ $icon }}" class="w-6 h-6"></i>
            </div>
            <div class="flex-1">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">{{ strtoupper($type) }}</p>
                <p class="text-sm font-semibold text-slate-900 leading-tight">{{ $message }}</p>
            </div>
            <button @click="show = false" class="text-slate-400 hover:text-slate-600 p-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
@endif

<style>
    @keyframes slideUp {
        from { transform: translateY(2rem); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .anima-slide-up {
        animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>