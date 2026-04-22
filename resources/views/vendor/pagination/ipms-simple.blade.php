@if ($paginator->hasPages())
    <nav class="app-pagination" role="navigation" aria-label="Pagination Navigation">
        <div class="app-pagination__meta">
            Page {{ $paginator->currentPage() }}
        </div>

        <div class="app-pagination__controls">
            @if ($paginator->onFirstPage())
                <span class="app-pagination__button app-pagination__button--disabled" aria-disabled="true">
                    Previous
                </span>
            @else
                <a class="app-pagination__button" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    Previous
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="app-pagination__button" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    Next
                </a>
            @else
                <span class="app-pagination__button app-pagination__button--disabled" aria-disabled="true">
                    Next
                </span>
            @endif
        </div>
    </nav>
@endif
