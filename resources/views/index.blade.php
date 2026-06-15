@extends('layouts.app')

@section('title', config('app.name', 'Rick & Morty Encyclopedia'))
@section('meta_description', 'Browse and search Rick and Morty characters, episodes, and locations.')

@section('hero')
    <div class="bg-zinc-900 border-b border-zinc-800 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold tracking-tight mb-3">
                Rick &amp; Morty <span class="text-green-400">Encyclopedia</span>
            </h1>
            <p class="text-zinc-400 text-lg max-w-xl mx-auto mb-8">
                Search and explore information from the world of Rick and Morty
            </p>
            <form action="{{ url('/characters') }}" method="GET" class="flex gap-2 max-w-lg mx-auto">
                <input
                    type="search"
                    name="search"
                    placeholder="Search characters…"
                    class="flex-1 px-4 py-2.5 rounded bg-zinc-800 text-zinc-100 placeholder-zinc-500 border border-zinc-700 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500"
                >
                <button type="submit" class="bg-green-500 hover:bg-green-400 text-zinc-900 font-semibold px-5 py-2.5 rounded transition-colors shadow shadow-green-500/20">
                    Search
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
        @foreach ([['826', 'Characters'], ['126', 'Locations'], ['51', 'Episodes'], ['3', 'Seasons']] as [$count, $label])
            <div class="bg-zinc-900 rounded-lg border border-zinc-800 p-5 text-center">
                <div class="text-2xl font-bold text-green-400">{{ $count }}</div>
                <div class="text-sm text-zinc-400 mt-1">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    {{-- Browse sections --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">

        <a href="{{ url('/characters') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-green-500/50 rounded-lg p-6 transition-all hover:shadow-lg hover:shadow-green-500/5">
            <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center mb-4 group-hover:bg-green-500/20 transition-colors">
                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                </svg>
            </div>
            <h2 class="font-semibold text-zinc-100 mb-1 group-hover:text-green-400 transition-colors">Characters</h2>
            <p class="text-sm text-zinc-500">Browse all characters with filtering by status, species, and gender.</p>
        </a>

        <a href="{{ url('/locations') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-green-500/50 rounded-lg p-6 transition-all hover:shadow-lg hover:shadow-green-500/5">
            <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center mb-4 group-hover:bg-green-500/20 transition-colors">
                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h2 class="font-semibold text-zinc-100 mb-1 group-hover:text-green-400 transition-colors">Locations</h2>
            <p class="text-sm text-zinc-500">Discover locations from Earth to alien worlds across the multiverse.</p>
        </a>

        <a href="{{ url('/episodes') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-green-500/50 rounded-lg p-6 transition-all hover:shadow-lg hover:shadow-green-500/5">
            <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center mb-4 group-hover:bg-green-500/20 transition-colors">
                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h2 class="font-semibold text-zinc-100 mb-1 group-hover:text-green-400 transition-colors">Episodes</h2>
            <p class="text-sm text-zinc-500">Explore all episodes across all seasons with full character appearances.</p>
        </a>
    </div>

    {{-- API callout --}}
    <section class="bg-zinc-900 border border-zinc-800 rounded-xl p-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <h2 class="text-lg font-semibold mb-2">Developer API</h2>
            <p class="text-zinc-400 text-sm max-w-md">
                Access Rick and Morty data programmatically via our REST API, powered by the public Rick and Morty API.
            </p>
        </div>
        <a href="https://rickandmortyapi.com/documentation" target="_blank" rel="noopener" class="shrink-0 bg-green-500 hover:bg-green-400 text-zinc-900 font-semibold px-6 py-3 rounded-lg transition-colors flex items-center gap-2 shadow shadow-green-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            View API Docs
        </a>
    </section>

@endsection
