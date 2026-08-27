@props(['paginator'])

@if ($paginator->total() > 0)
    <div {{ $attributes->merge(['class' => 'mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between']) }}>
        <p class="text-sm text-gray-500">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} од {{ number_format($paginator->total()) }}
        </p>
        <nav class="flex flex-wrap items-center gap-1 text-sm">
            @if ($paginator->onFirstPage())
                <span class="cursor-not-allowed rounded-md border border-gray-200 px-3 py-1.5 text-gray-400">Претходна</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="rounded-md border border-gray-300 px-3 py-1.5 hover:bg-gray-50">Претходна</a>
            @endif

            @if ($paginator->lastPage() > 1)
                @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}"
                        class="rounded-md border px-3 py-1.5 {{ $page === $paginator->currentPage() ? 'border-my-purple bg-my-purple font-medium text-white' : 'border-gray-300 hover:bg-gray-50' }}">{{ $page }}</a>
                @endforeach
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="rounded-md border border-gray-300 px-3 py-1.5 hover:bg-gray-50">Следна</a>
            @else
                <span class="cursor-not-allowed rounded-md border border-gray-200 px-3 py-1.5 text-gray-400">Следна</span>
            @endif
        </nav>
    </div>
@endif
