<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiConnectionException;
use App\Exceptions\ApiRateLimitException;
use App\Http\Requests\SearchLocationsRequest;
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
     * @param  SearchLocationsRequest  $request  Supported query params: search, type, dimension, page
     * @return View
     */
    public function index(SearchLocationsRequest $request): View
    {
        $filters = [
            'search'    => $request->string('search')->toString(),
            'type'      => $request->string('type')->toString(),
            'dimension' => $request->string('dimension')->toString(),
        ];

        $currentPage = max(1, $request->integer('page', 1));

        $error = null;

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
        } catch (ApiRateLimitException) {
            $locations     = [];
            $info          = [];
            $filterOptions = [];
            $error         = 'The API rate limit has been reached. Please wait a moment and try again.';
        } catch (ApiConnectionException) {
            $locations     = [];
            $info          = [];
            $filterOptions = [];
            $error         = 'Unable to reach the Rick and Morty API. Please try again later.';
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
                'error'         => $error,
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
        try {
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
        } catch (ApiRateLimitException) {
            abort(503, 'The API rate limit has been reached. Please try again in a moment.');
        } catch (ApiConnectionException) {
            abort(503, 'Unable to reach the Rick and Morty API. Please try again later.');
        }

        return view('locations.show', array_merge(
            $this->paginationData($currentPage, $totalPages),
            compact('location', 'residents')
        ));
    }
}
