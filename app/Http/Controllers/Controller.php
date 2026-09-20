<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Laravel's default `Resource::collection($paginator)` response nests
     * pagination info under `data` + `links` + `meta`. The frontend's
     * `PaginatedResponse<T>` type expects everything flat at the top level
     * (current_page, last_page, ... alongside data) — the shape a raw
     * `$paginator->toArray()` produces without going through a Resource.
     * This bridges the two: real Resources (for field control/hiding) with
     * the flat shape the frontend actually reads.
     */
    protected function paginated(LengthAwarePaginator $paginator, string $resourceClass): array
    {
        return [
            'data' => $resourceClass::collection($paginator->items()),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
        ];
    }
}
