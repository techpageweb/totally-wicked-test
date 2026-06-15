<?php

namespace App\Http\Controllers;

use App\Services\RickAndMortyService;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    public function __construct(private RickAndMortyService $api) {}

    public function index(Request $request)
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

    public function show(int $id)
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
