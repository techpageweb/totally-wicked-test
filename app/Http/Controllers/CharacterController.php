<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiRateLimitException;
use App\Services\RickAndMortyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles character listing and detail pages.
 */
class CharacterController extends Controller
{
    public function __construct(private RickAndMortyService $api) {}

    /**
     * Display a paginated, filterable list of characters.
     *
     * @param  Request  $request  Supported query params: search, status, species, gender, page
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'  => $request->string('search')->toString(),
            'status'  => $request->string('status')->toString(),
            'species' => $request->string('species')->toString(),
            'gender'  => $request->string('gender')->toString(),
        ];

        $currentPage = max(1, $request->integer('page', 1));

        try {
            $data = $this->api->getCharacters(array_filter([
                'page'    => $currentPage,
                'name'    => $filters['search'],
                'status'  => $filters['status'],
                'species' => $filters['species'],
                'gender'  => $filters['gender'],
            ]));

            $characters    = $data['results'] ?? [];
            $info          = $data['info'] ?? [];
            $filterOptions = $this->api->getCharacterFilterOptions();
            $rateLimited   = false;
        } catch (ApiRateLimitException) {
            $characters    = [];
            $info          = [];
            $filterOptions = [];
            $rateLimited   = true;
        }

        $totalPages  = $info['pages'] ?? 1;
        $filterQuery = http_build_query(array_filter($filters));

        $window      = 2;
        $pageNumbers = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i === 1 || $i === $totalPages || ($i >= $currentPage - $window && $i <= $currentPage + $window)) {
                $pageNumbers[] = $i;
            }
        }

        return view('characters.index', [
            'characters'    => $characters,
            'info'          => $info,
            'filters'       => $filters,
            'filterOptions' => $filterOptions,
            'currentPage'   => $currentPage,
            'totalPages'    => $totalPages,
            'pageNumbers'   => $pageNumbers,
            'filterQuery'   => $filterQuery,
            'rateLimited'   => $rateLimited,
        ]);
    }

    /**
     * Display a single character with their episode appearances.
     *
     * Aborts with 404 if the character is not found in the API.
     */
    public function show(int $id): View
    {
        $character = $this->api->getCharacter($id);

        if (empty($character)) {
            abort(404);
        }

        $episodeIds = array_map(
            fn ($url) => (int) basename($url),
            $character['episode'] ?? []
        );

        $episodes = $this->api->getMultipleEpisodes($episodeIds);

        return view('characters.show', compact('character', 'episodes'));
    }
}
