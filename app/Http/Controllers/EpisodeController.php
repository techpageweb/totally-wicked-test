<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiConnectionException;
use App\Exceptions\ApiRateLimitException;
use App\Http\Requests\SearchEpisodesRequest;
use App\Services\CharacterService;
use App\Services\EpisodeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles episode listing and detail pages.
 */
class EpisodeController extends Controller
{
    /**
     * @param  EpisodeService    $episodes    Injected episode API service.
     * @param  CharacterService  $characters  Injected character API service (for episode detail page).
     */
    public function __construct(
        private EpisodeService $episodes,
        private CharacterService $characters,
    ) {}

    /**
     * Display a paginated, filterable list of episodes.
     *
     * @param  SearchEpisodesRequest  $request  Supported query params: search, episode, page
     * @return View
     */
    public function index(SearchEpisodesRequest $request): View
    {
        $filters = [
            'search'  => $request->string('search')->toString(),
            'episode' => $request->string('episode')->toString(),
        ];

        $currentPage = max(1, $request->integer('page', 1));

        $error = null;

        try {
            $data = $this->episodes->getEpisodes(array_filter([
                'page'    => $currentPage,
                'name'    => $filters['search'],
                'episode' => $filters['episode'],
            ]));

            $episodes = $data['results'] ?? [];
            $info     = $data['info'] ?? [];
        } catch (ApiRateLimitException) {
            $episodes = [];
            $info     = [];
            $error    = 'The API rate limit has been reached. Please wait a moment and try again.';
        } catch (ApiConnectionException) {
            $episodes = [];
            $info     = [];
            $error    = 'Unable to reach the Rick and Morty API. Please try again later.';
        }

        $filterQuery = http_build_query(array_filter($filters));

        return view('episodes.index', array_merge(
            $this->paginationData($currentPage, $info['pages'] ?? 1),
            [
                'episodes'    => $episodes,
                'info'        => $info,
                'filters'     => $filters,
                'filterQuery' => $filterQuery,
                'error'       => $error,
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
        try {
            $episode = $this->episodes->getEpisode($id);

            if (empty($episode)) {
                abort(404);
            }

            $perPage     = 20;
            $currentPage = max(1, $request->integer('page', 1));
            $allIds      = array_map(fn ($url) => (int) basename($url), $episode['characters'] ?? []);
            $totalPages  = max(1, (int) ceil(count($allIds) / $perPage));
            $currentPage = min($currentPage, $totalPages);
            $pageIds     = array_slice($allIds, ($currentPage - 1) * $perPage, $perPage);
            $characters  = $this->characters->getMultipleCharacters($pageIds);
        } catch (ApiRateLimitException) {
            abort(503, 'The API rate limit has been reached. Please try again in a moment.');
        } catch (ApiConnectionException) {
            abort(503, 'Unable to reach the Rick and Morty API. Please try again later.');
        }

        return view('episodes.show', array_merge(
            $this->paginationData($currentPage, $totalPages),
            compact('episode', 'characters')
        ));
    }
}
