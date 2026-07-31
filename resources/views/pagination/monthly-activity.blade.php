@if ($paginator->hasPages())
    <nav class="activity-pager" role="navigation" aria-label="Monthly record activity pagination">
        <div class="activity-pager-summary">
            Showing
            <strong>{{ $paginator->firstItem() }}</strong>
            to
            <strong>{{ $paginator->lastItem() }}</strong>
            of
            <strong>{{ $paginator->total() }}</strong>
            records
        </div>

        <div class="activity-pager-links">
            @if ($paginator->onFirstPage())
                <span class="activity-page-disabled" aria-disabled="true">
                    <i class="fa-solid fa-chevron-left"></i>
                    <span>Previous</span>
                </span>
            @else
                <a class="activity-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <i class="fa-solid fa-chevron-left"></i>
                    <span>Previous</span>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="activity-page-ellipsis">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="activity-page-current activity-page-number" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="activity-page-link activity-page-number" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="activity-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    <span>Next</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            @else
                <span class="activity-page-disabled" aria-disabled="true">
                    <span>Next</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
