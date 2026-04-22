@if ($paginator->hasPages())
    <nav class="app-pagination" role="navigation" aria-label="Pagination Navigation">
        <div class="app-pagination__meta">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </div>

        <div class="app-pagination__controls">
            @if ($paginator->onFirstPage())
                <span class="app-pagination__button app-pagination__button--disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    Previous
                </span>
            @else
                <a class="app-pagination__button" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                    Previous
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="app-pagination__ellipsis" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="app-pagination__button app-pagination__button--active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="app-pagination__button" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="app-pagination__button" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                    Next
                </a>
            @else
                <span class="app-pagination__button app-pagination__button--disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    Next
                </span>
            @endif
        </div>
    </nav>
@endif
