@if ($paginator->hasPages())
    <div class="flex items-center justify-between">

        <!-- INFO -->
        <div class="text-sm text-slate-500">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }}
        </div>

        <!-- BUTTONS -->
        <div class="flex items-center gap-1">

            {{-- PREVIOUS --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1 text-sm text-slate-300 border rounded">
                    Prev
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="px-3 py-1 text-sm border rounded hover:bg-slate-100">
                    Prev
                </a>
            @endif

            {{-- PAGES --}}
            @foreach ($elements as $element)
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-1 text-sm bg-indigo-600 text-white rounded">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               class="px-3 py-1 text-sm border rounded hover:bg-slate-100">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- NEXT --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="px-3 py-1 text-sm border rounded hover:bg-slate-100">
                    Next
                </a>
            @else
                <span class="px-3 py-1 text-sm text-slate-300 border rounded">
                    Next
                </span>
            @endif

        </div>

    </div>
@endif