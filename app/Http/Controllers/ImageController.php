<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Proxies and caches character avatar images from the Rick and Morty API.
 *
 * Images are stored in local storage on first request so subsequent
 * requests are served entirely from disk without hitting the API.
 */
class ImageController extends Controller
{
    public function characterAvatar(int $id): Response
    {
        $path = "character-images/{$id}.jpeg";

        if (! Storage::exists($path)) {
            try {
                $response = Http::timeout(10)->get(
                    config('rickandmorty.base_url') . "/character/avatar/{$id}.jpeg"
                );
            } catch (ConnectionException) {
                abort(502, 'Could not reach image source.');
            }

            if ($response->failed()) {
                abort($response->status());
            }

            Storage::put($path, $response->body());
        }

        return response(Storage::get($path), 200, [
            'Content-Type'  => 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
