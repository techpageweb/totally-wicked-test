<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiRateLimitException;
use App\Services\RickAndMortyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles location listing and detail pages.
 */
class LocationController extends Controller
{
    /**
     * @param  RickAndMortyService  $api  Injected API service.
     */
    public function __construct(private RickAndMortyService $api) {}

    /**
     * Display a paginated, filterable list of locations.
     *
     * @param  Request  $request  Supported query params: search, type, dimension, page
     * @return View
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'    => $request->string('search')->toString(),
            'type'      => $request->string('type')->toString(),
            'dimension' => $request->string('dimension')->toString(),
        ];

        $currentPage = max(1, $request->integer('page', 1));

        try {
            $data = $this->api->getLocations(array_filter([
                'page'      => $currentPage,
                'name'      => $filters['search'],
                'type'      => $filters['type'],
                'dimension' => $filters['dimension'],
            ]));

            $locations     = $data['results'] ?? [];
            $info          = $data['info'] ?? [];
            $filterOptions = $this->api->getLocationFilterOptions();
            $rateLimited   = false;
        } catch (ApiRateLimitException) {
            $locations     = [];
            $info          = [];
            $filterOptions = [];
            $rateLimited   = true;
        }

        $filterQuery = http_build_query(array_filter($filters));

        return view('locations.index', array_merge(
            $this->paginationData($currentPage, $info['pages'] ?? 1),
            [
                'locations'     => $locations,
                'info'          => $info,
                'filters'       => $filters,
                'filterOptions' => $filterOptions,
                'filterQuery'   => $filterQuery,
                'rateLimited'   => $rateLimited,
            ]
        ));
    }

    /**
     * Display a single location with a paginated list of its residents (20 per page).
     *
     * @param  int      $id       Location ID.
     * @param  Request  $request  Supports query param: page
     * @return View
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function show(int $id, Request $request): View
    {
        $location = $this->api->getLocation($id);

        if (empty($location)) {
            abort(404);
        }

        $perPage     = 20;
        $currentPage = max(1, $request->integer('page', 1));
        $allIds      = array_map(fn ($url) => (int) basename($url), $location['residents'] ?? []);
        $totalPages  = max(1, (int) ceil(count($allIds) / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $pageIds     = array_slice($allIds, ($currentPage - 1) * $perPage, $perPage);
        $residents   = $this->api->getMultipleCharacters($pageIds);

        return view('locations.show', array_merge(
            $this->paginationData($currentPage, $totalPages),
            compact('location', 'residents')
        ));
    }
}
