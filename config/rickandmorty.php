<?php

return [
    'base_url'         => env('RICK_AND_MORTY_API_URL', 'https://rickandmortyapi.com/api'),
    'cache_ttl'        => env('RICK_AND_MORTY_CACHE_TTL', 3600),
    'filter_cache_ttl' => env('RICK_AND_MORTY_FILTER_CACHE_TTL', 86400),
];
