<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiConnectionException;
use App\Exceptions\ApiRateLimitException;
use App\Services\StatsService;
use Illuminate\View\View;

/**
 * Handles the home/landing page.
 */
class HomeController extends Controller
{
    /**
     * @param  StatsService  $stats  Injected stats service.
     */
    public function __construct(private StatsService $stats) {}

    /**
     * Display the home page with live character, episode, and location counts.
     *
     * @return View
     */
    public function index(): View
    {
        try {
            $stats = $this->stats->getStats();
        } catch (ApiRateLimitException|ApiConnectionException) {
            $stats = ['characters' => null, 'episodes' => null, 'locations' => null];
        }

        return view('index', compact('stats'));
    }
}
