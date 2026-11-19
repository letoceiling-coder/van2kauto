@if ($paginator->hasPages())
    <nav class="che168-pager" aria-label="Страницы">
        <p class="che168-pager-meta">
            @if ($paginator->total())
                {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} из {{ $paginator->total() }}
            @else
                Нет записей
            @endif
        </p>
        <ul class="che168-pager-pages">
            @if ($paginator->onFirstPage())
                <li><span class="is-disabled" aria-hidden="true">‹</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Назад">‹</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="is-gap">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="is-current" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Вперёд">›</a></li>
            @else
                <li><span class="is-disabled" aria-hidden="true">›</span></li>
            @endif
        </ul>
    </nav>
@endif