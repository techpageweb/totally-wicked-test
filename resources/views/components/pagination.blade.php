@props([
    'currentPage',
    'totalPages',
    'pageNumbers',
    'filterQuery' => '',
    'baseUrl',
])

@if ($totalPages > 1)
    @php
        $pageUrl = fn($p) => url($baseUrl) . '?' . ($filterQuery ? $filterQuery . '&page=' . $p : 'page=' . $p);
    @endphp

    <div class="flex items-center justify-center gap-1 flex-wrap mt-2">

        @if ($currentPage > 1)
            <a href="{{ $pageUrl($currentPage - 1) }}"
               class="px-3 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 rounded transition-colors text-sm">
                &larr;
            </a>
        @else
            <span class="px-3 py-2 bg-zinc-900 text-zinc-600 rounded text-sm cursor-default">&larr;</span>
        @endif

        @php $prev = null; @endphp
        @foreach ($pageNumbers as $page)
            @if ($prev !== null && $page - $prev > 1)
                <span class="px-2 py-2 text-zinc-500 text-sm select-none">&hellip;</span>
            @endif

            @if ($page === $currentPage)
                <span class="px-3 py-2 bg-green-500 text-zinc-900 font-semibold rounded text-sm">{{ $page }}</span>
            @else
                <a href="{{ $pageUrl($page) }}"
                   class="px-3 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 rounded transition-colors text-sm">
                    {{ $page }}
                </a>
            @endif

            @php $prev = $page; @endphp
        @endforeach

        @if ($currentPage < $totalPages)
            <a href="{{ $pageUrl($currentPage + 1) }}"
               class="px-3 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 rounded transition-colors text-sm">
                &rarr;
            </a>
        @else
            <span class="px-3 py-2 bg-zinc-900 text-zinc-600 rounded text-sm cursor-default">&rarr;</span>
        @endif

    </div>
@endif
