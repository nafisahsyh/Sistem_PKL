@php
    // ===========================
    // CUSTOM GROUPED PAGINATION
    // ===========================
    $groupSize = 2; // berapa link halaman per grup
    $currentGroup = ceil($paginator->currentPage() / $groupSize);
    $start = ($currentGroup - 1) * $groupSize + 1;
    $end = min($start + $groupSize - 1, $paginator->lastPage());
@endphp

@if ($paginator->hasPages())
    <div class="d-flex justify-content-center align-items-center mb-3 gap-3">
        {{-- Info teks --}}
        <div style="color: #6c757d; font-size: 0.85rem;">
            Menampilkan {{ $paginator->firstItem() }} sampai {{ $paginator->lastItem() }} dari {{ $paginator->total() }}
        </div>

        {{-- Pagination --}}
        <ul class="pagination mb-0">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
                </li>
            @endif

            {{-- Page Links (dikelompokkan) --}}
            @for ($i = $start; $i <= $end; $i++)
                <li class="page-item {{ $i == $paginator->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                </li>
            @endfor

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
                </li>
            @else
                <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
            @endif
        </ul>
    </div>
@endif
