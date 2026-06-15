@extends('layouts.app')

@section('title', 'Episodes — ' . config('app.name'))

@section('content')

    <x-rate-limit-error :message="$error" />

    {{-- Header & Search --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-100 mb-6">Episodes</h1>

        <form method="GET" action="{{ url('/episodes') }}" class="flex flex-col gap-3">
            <div class="flex flex-col sm:flex-row gap-3">
                <input
                    type="search"
                    name="search"
                    value="{{ old('search', $filters['search']) }}"
                    placeholder="Search by name…"
                    class="flex-1 px-4 py-2 rounded bg-zinc-800 text-zinc-100 placeholder-zinc-500 border focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors {{ $errors->has('search') ? 'border-red-500 focus:border-red-500' : 'border-zinc-700 focus:border-green-500' }}"
                >

                <input
                    type="search"
                    name="episode"
                    value="{{ old('episode', $filters['episode']) }}"
                    placeholder="Episode code, e.g. S01E01…"
                    class="flex-1 px-4 py-2 rounded bg-zinc-800 text-zinc-100 placeholder-zinc-500 border focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors {{ $errors->has('episode') ? 'border-red-500 focus:border-red-500' : 'border-zinc-700 focus:border-green-500' }}"
                >

                <button type="submit" class="px-5 py-2 bg-green-500 hover:bg-green-400 text-zinc-900 font-semibold rounded transition-colors">
                    Filter
                </button>

                @if (array_filter($filters))
                    <a href="{{ url('/episodes') }}" class="px-5 py-2 bg-zinc-700 hover:bg-zinc-600 text-zinc-200 rounded transition-colors text-center">
                        Clear
                    </a>
                @endif
            </div>

            @error('search')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
            @error('episode')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
        </form>
    </div>

    {{-- Results count --}}
    @if (!empty($info))
        <p class="text-sm text-zinc-500 mb-6">{{ number_format($info['count']) }} episodes found</p>
    @endif

    {{-- Episode grid --}}
    @if (!empty($episodes))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">
            @foreach ($episodes as $episode)
                <a href="{{ url('/episodes/' . $episode['id']) }}"
                   class="group bg-zinc-900 border border-zinc-800 hover:border-green-500/50 rounded-lg p-5 transition-all hover:shadow-lg hover:shadow-green-500/5 flex flex-col gap-3">

                    <div class="flex items-start justify-between gap-3">
                        <h2 class="text-base font-semibold text-zinc-100 group-hover:text-green-400 transition-colors leading-snug">
                            {{ $episode['name'] }}
                        </h2>
                        <span class="shrink-0 text-xs font-mono text-green-400 bg-green-500/10 border border-green-500/20 rounded px-2 py-1">
                            {{ $episode['episode'] }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-xs text-zinc-500">
                        <span>{{ $episode['air_date'] }}</span>
                        <span>{{ count($episode['characters']) }} characters</span>
                    </div>

                </a>
            @endforeach
        </div>

        <x-pagination
            :currentPage="$currentPage"
            :totalPages="$totalPages"
            :pageNumbers="$pageNumbers"
            :filterQuery="$filterQuery"
            baseUrl="/episodes"
        />

    @else
        <div class="text-center py-20 text-zinc-500">
            <p class="text-lg mb-2">No episodes found</p>
            <a href="{{ url('/episodes') }}" class="text-sm text-green-500 hover:text-green-400 transition-colors">Clear filters</a>
        </div>
    @endif

@endsection
