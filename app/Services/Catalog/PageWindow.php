<?php

namespace App\Services\Catalog;

use Illuminate\Http\Request;

class PageWindow
{
    public function __construct(
        public readonly int $limit,
        public readonly int $offset,
        public readonly int $page,
    ) {}

    public static function from(Request $request, int $defaultLimit = 24, int $maxLimit = 100): self
    {
        $limit = (int) $request->input('limit', $request->input('per_page', $defaultLimit));
        $limit = min($maxLimit, max(1, $limit));

        if ($request->filled('offset')) {
            $offset = max(0, (int) $request->input('offset'));
            $page = intdiv($offset, $limit) + 1;
        } else {
            $page = max(1, (int) $request->input('page', 1));
            $offset = ($page - 1) * $limit;
        }

        return new self($limit, $offset, $page);
    }

    /** @return array<string, int|bool|null> */
    public function meta(int $total): array
    {
        $hasMore = $this->offset + $this->limit < $total;

        return [
            'total' => $total,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'page' => $this->page,
            'pages' => $this->limit > 0 ? (int) ceil($total / $this->limit) : 0,
            'has_more' => $hasMore,
            'next_offset' => $hasMore ? $this->offset + $this->limit : null,
        ];
    }
}
