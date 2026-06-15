<?php

namespace App\Http\Controllers;

use App\Services\RickAndMortyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles location listing and detail pages.
 */
class LocationController extends Controller
{
    public function __construct(private RickAndMortyService $api) {}

    /**
     * Display a paginated, filterable list of locations.
     *
     * @param  Request  $request  Supported query params: search, type, dimension, page
     */
    public function index(Request $request): View
    {
        $params = array_filter([
            'page'      => $request->integer('page', 1),
            'name'      => $request->string('search')->toString(),
            'type'      => $request->string('type')->toString(),
            'dimension' => $request->string('dimension')->toString(),
        ]);

        $data = $this->api->getLocations($params);

        return view('locations.index', [
            'locations' => $data['results'] ?? [],
            'info'      => $data['info'] ?? [],
            'filters'   => $request->only(['search', 'type', 'dimension']),
        ]);
    }

    /**
     * Display a single location.
     *
     * Aborts with 404 if the location is not found in the API.
     */
    public function show(int $id): View
    {
        $location = $this->api->getLocation($id);

        if (empty($location)) {
            abort(404);
        }

        return view('locations.show', compact('location'));
    }
}
