@php
    // ===========================
    // CUSTOM GROUPED PAGINATION
    // ===========================
    $groupSize = 1; // berapa link halaman per grup
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

        <ul class="pagination mb-0 align-items-center">

            {{-- FIRST --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url(1) }}">&laquo;&laquo;</a>
                </li>
            @endif

            {{-- PREVIOUS --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}">&laquo;</a>
                </li>
            @endif

            {{-- PAGE LINKS (dengan input pada halaman aktif) --}}
            @for ($i = $start; $i <= $end; $i++)
                @if ($i == $paginator->currentPage())
                    <li class="page-item active d-flex align-items-center">

                        <form action="{{ request()->url() }}" method="GET" class="m-0 p-0 d-inline-block"
                            style="vertical-align: middle;">

                            {{-- Pertahankan filter / query lain --}}
                            @foreach (request()->except('page') as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach

                            <input type="number" name="page" value="{{ $paginator->currentPage() }}" min="1"
                                max="{{ $paginator->lastPage() }}" class="goto-input text-center" style="cursor:text;">

                        </form>

                        {{-- Teks "of X" --}}
                    <li class="page-item disabled d-flex align-items-center">
                        <span class="px-1 text-secondary small" style="pointer-events:none; cursor:default;">
                            of {{ $paginator->lastPage() }}
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                    </li>
                @endif
            @endfor

            {{-- NEXT --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}">&raquo;</a>
                </li>
            @else
                <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
            @endif

            {{-- LAST --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">&raquo;&raquo;</a>
                </li>
            @else
                <li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>
            @endif

        </ul>
    </div>

    {{-- Auto-submit saat enter / blur --}}
    <script>
        document.querySelectorAll('input[name="page"]').forEach(input => {
            let form = input.closest('form');

            function submitPage() {
                let page = parseInt(input.value);
                let min = parseInt(input.min);
                let max = parseInt(input.max);

                if (isNaN(page) || page < min) page = min;
                if (page > max) page = max;

                input.value = page;
                form.submit();
            }

            input.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitPage();
                }
            });

            input.addEventListener('blur', submitPage);
        });
    </script>
@endif
