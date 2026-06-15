<?php

namespace App\Http\Controllers;

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
        $params = array_filter([
            'page'    => $request->integer('page', 1),
            'name'    => $request->string('search')->toString(),
            'status'  => $request->string('status')->toString(),
            'species' => $request->string('species')->toString(),
            'gender'  => $request->string('gender')->toString(),
        ]);

        $data = $this->api->getCharacters($params);

        return view('characters.index', [
            'characters' => $data['results'] ?? [],
            'info'       => $data['info'] ?? [],
            'filters'    => $request->only(['search', 'status', 'species', 'gender']),
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
