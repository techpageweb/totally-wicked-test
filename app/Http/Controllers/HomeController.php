<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiConnectionException;
use App\Exceptions\ApiRateLimitException;
use App\Services\RickAndMortyService;
use Illuminate\View\View;

/**
 * Handles the home/landing page.
 */
class HomeController extends Controller
{
    /**
     * @param  RickAndMortyService  $api  Injected API service.
     */
    public function __construct(private RickAndMortyService $api) {}

    /**
     * Display the home page with live character, episode, and location counts.
     *
     * @return View
     */
    public function index(): View
    {
        try {
            $stats = $this->api->getStats();
        } catch (ApiRateLimitException|ApiConnectionException) {
            $stats = ['characters' => null, 'episodes' => null, 'locations' => null];
        }

        return view('index', compact('stats'));
    }
}
