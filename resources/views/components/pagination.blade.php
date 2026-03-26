@props(['paginator'])

@if ($paginator->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="mb-2 mb-md-0">
            <p class="mb-0 text-muted small">
                Showing <span class="fw-semibold">{{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}</span> of 
                <span class="fw-semibold">{{ $paginator->total() }}</span>
            </p>
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm flex-wrap justify-content-center mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled me-1">
                        <span class="page-link">&lsaquo;</span>
                    </li>
                @else
                    <li class="page-item me-1">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a>
                    </li>
                @endif

                {{-- First Page Link --}}
                @if($paginator->currentPage() > 3)
                    <li class="page-item me-1">
                        <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                    </li>
                    @if($paginator->currentPage() > 4)
                        <li class="page-item disabled me-1">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                @endif

                {{-- Pagination Elements --}}
                @foreach(range(1, $paginator->lastPage()) as $i)
                    @if($i >= $paginator->currentPage() - 2 && $i <= $paginator->currentPage() + 2)
                        @if ($i == $paginator->currentPage())
                            <li class="page-item active me-1" aria-current="page">
                                <span class="page-link">{{ $i }}</span>
                            </li>
                        @else
                            <li class="page-item me-1">
                                <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                            </li>
                        @endif
                    @endif
                @endforeach

                {{-- Last Page Link --}}
                @if($paginator->currentPage() < $paginator->lastPage() - 2)
                    @if($paginator->currentPage() < $paginator->lastPage() - 3)
                        <li class="page-item disabled me-1">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                    <li class="page-item me-1">
                        <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">&rsaquo;</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif
