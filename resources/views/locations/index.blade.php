@extends('layouts.app')

@section('title', 'Locations — ' . config('app.name'))

@section('content')

    <x-rate-limit-error :rateLimited="$rateLimited" />

    {{-- Header & Search --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-100 mb-6">Locations</h1>

        <form method="GET" action="{{ url('/locations') }}" class="flex flex-col sm:flex-row gap-3">
            <input
                type="search"
                name="search"
                value="{{ $filters['search'] }}"
                placeholder="Search by name…"
                class="flex-1 px-4 py-2 rounded bg-zinc-800 text-zinc-100 placeholder-zinc-500 border border-zinc-700 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500"
            >

            <select name="type" class="px-4 py-2 rounded bg-zinc-800 text-zinc-100 border border-zinc-700 focus:outline-none focus:border-green-500">
                <option value="">All Types</option>
                @foreach ($filterOptions['type'] ?? [] as $option)
                    <option value="{{ $option }}" @selected($filters['type'] === $option)>{{ $option }}</option>
                @endforeach
            </select>

            <select name="dimension" class="px-4 py-2 rounded bg-zinc-800 text-zinc-100 border border-zinc-700 focus:outline-none focus:border-green-500">
                <option value="">All Dimensions</option>
                @foreach ($filterOptions['dimension'] ?? [] as $option)
                    <option value="{{ $option }}" @selected($filters['dimension'] === $option)>{{ $option }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-5 py-2 bg-green-500 hover:bg-green-400 text-zinc-900 font-semibold rounded transition-colors">
                Filter
            </button>

            @if (array_filter($filters))
                <a href="{{ url('/locations') }}" class="px-5 py-2 bg-zinc-700 hover:bg-zinc-600 text-zinc-200 rounded transition-colors text-center">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Results count --}}
    @if (!empty($info))
        <p class="text-sm text-zinc-500 mb-6">{{ number_format($info['count']) }} locations found</p>
    @endif

    {{-- Location grid --}}
    @if (!empty($locations))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">
            @foreach ($locations as $location)
                <a href="{{ url('/locations/' . $location['id']) }}"
                   class="group bg-zinc-900 border border-zinc-800 hover:border-green-500/50 rounded-lg p-5 transition-all hover:shadow-lg hover:shadow-green-500/5 flex flex-col gap-3">

                    <div class="flex items-start justify-between gap-3">
                        <h2 class="text-base font-semibold text-zinc-100 group-hover:text-green-400 transition-colors leading-snug">
                            {{ $location['name'] }}
                        </h2>
                        @if ($location['type'] !== 'unknown' && $location['type'] !== '')
                            <span class="shrink-0 text-xs text-zinc-400 bg-zinc-800 border border-zinc-700 rounded px-2 py-1">
                                {{ $location['type'] }}
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between text-xs text-zinc-500">
                        <span>{{ $location['dimension'] ?: 'Unknown dimension' }}</span>
                        <span>{{ count($location['residents']) }} residents</span>
                    </div>

                </a>
            @endforeach
        </div>

        <x-pagination
            :currentPage="$currentPage"
            :totalPages="$totalPages"
            :pageNumbers="$pageNumbers"
            :filterQuery="$filterQuery"
            baseUrl="/locations"
        />

    @else
        <div class="text-center py-20 text-zinc-500">
            <p class="text-lg mb-2">No locations found</p>
            <a href="{{ url('/locations') }}" class="text-sm text-green-500 hover:text-green-400 transition-colors">Clear filters</a>
        </div>
    @endif

@endsection
