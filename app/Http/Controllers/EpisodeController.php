<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiRateLimitException;
use App\Services\RickAndMortyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles episode listing and detail pages.
 */
class EpisodeController extends Controller
{
    /**
     * @param  RickAndMortyService  $api  Injected API service.
     */
    public function __construct(private RickAndMortyService $api) {}

    /**
     * Display a paginated, filterable list of episodes.
     *
     * @param  Request  $request  Supported query params: search, episode, page
     * @return View
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'  => $request->string('search')->toString(),
            'episode' => $request->string('episode')->toString(),
        ];

        $currentPage = max(1, $request->integer('page', 1));

        try {
            $data = $this->api->getEpisodes(array_filter([
                'page'    => $currentPage,
                'name'    => $filters['search'],
                'episode' => $filters['episode'],
            ]));

            $episodes    = $data['results'] ?? [];
            $info        = $data['info'] ?? [];
            $rateLimited = false;
        } catch (ApiRateLimitException) {
            $episodes    = [];
            $info        = [];
            $rateLimited = true;
        }

        $filterQuery = http_build_query(array_filter($filters));

        return view('episodes.index', array_merge(
            $this->paginationData($currentPage, $info['pages'] ?? 1),
            [
                'episodes'    => $episodes,
                'info'        => $info,
                'filters'     => $filters,
                'filterQuery' => $filterQuery,
                'rateLimited' => $rateLimited,
            ]
        ));
    }

    /**
     * Display a single episode with a paginated list of its characters (20 per page).
     *
     * @param  int      $id       Episode ID.
     * @param  Request  $request  Supports query param: page
     * @return View
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function show(int $id, Request $request): View
    {
        $episode = $this->api->getEpisode($id);

        if (empty($episode)) {
            abort(404);
        }

        $perPage     = 20;
        $currentPage = max(1, $request->integer('page', 1));
        $allIds      = array_map(fn ($url) => (int) basename($url), $episode['characters'] ?? []);
        $totalPages  = max(1, (int) ceil(count($allIds) / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $pageIds     = array_slice($allIds, ($currentPage - 1) * $perPage, $perPage);
        $characters  = $this->api->getMultipleCharacters($pageIds);

        return view('episodes.show', array_merge(
            $this->paginationData($currentPage, $totalPages),
            compact('episode', 'characters')
        ));
    }
}
