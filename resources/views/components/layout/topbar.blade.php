@props([
    'title' => 'Dashboard',
    'subtitle' => null,
])

<header class="h-16 border-b border-zinc-200 bg-white/80 backdrop-blur-md sticky top-0 z-40 flex items-center justify-between px-6 lg:px-10">
    <div class="flex flex-col">
        <h1 class="text-lg font-bold text-zinc-900 tracking-tight">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-[11px] text-zinc-500 font-medium uppercase tracking-wider">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="flex items-center gap-4">
        <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-zinc-100 rounded-full text-zinc-400 group cursor-pointer transition-colors hover:bg-zinc-200 border border-transparent hover:border-zinc-300">
            @include('components.icons.search', ['size' => 14])
            <span class="text-xs font-medium text-zinc-500">Search anything...</span>
            <span class="ml-4 text-[10px] font-bold text-zinc-400 bg-zinc-200 px-1.5 py-0.5 rounded border border-zinc-300">⌘K</span>
        </div>

        <div class="flex items-center gap-2">
            <button class="p-2 text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 rounded-lg transition-colors relative">
                @include('components.icons.bell', ['size' => 20])
                <span class="absolute top-2 right-2.5 h-2 w-2 rounded-full bg-blue-500 border-2 border-white"></span>
            </button>

            <div class="h-8 w-px bg-zinc-200 mx-1"></div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-2 text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 rounded-lg transition-colors group" title="Sign Out">
                    <span class="group-hover:translate-x-0.5 transition-transform inline-block">
                        @include('components.icons.logout', ['size' => 20])
                    </span>
                </button>
            </form>
        </div>
    </div>
</header>
