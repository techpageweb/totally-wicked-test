<?php

namespace App\Http\Controllers;

use App\Services\RickAndMortyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles episode listing and detail pages.
 */
class EpisodeController extends Controller
{
    public function __construct(private RickAndMortyService $api) {}

    /**
     * Display a paginated, filterable list of episodes.
     *
     * @param  Request  $request  Supported query params: search, episode, page
     */
    public function index(Request $request): View
    {
        $params = array_filter([
            'page'    => $request->integer('page', 1),
            'name'    => $request->string('search')->toString(),
            'episode' => $request->string('episode')->toString(),
        ]);

        $data = $this->api->getEpisodes($params);

        return view('episodes.index', [
            'episodes' => $data['results'] ?? [],
            'info'     => $data['info'] ?? [],
            'filters'  => $request->only(['search', 'episode']),
        ]);
    }

    /**
     * Display a single episode.
     *
     * Aborts with 404 if the episode is not found in the API.
     */
    public function show(int $id): View
    {
        $episode = $this->api->getEpisode($id);

        if (empty($episode)) {
            abort(404);
        }

        return view('episodes.show', compact('episode'));
    }
}
