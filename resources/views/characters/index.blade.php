@extends('layouts.app')

@section('title', 'Characters — ' . config('app.name'))

@section('content')

    <x-rate-limit-error :message="$error" />

    {{-- Header & Search --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-100 mb-6">Characters</h1>

        <form method="GET" action="{{ url('/characters') }}" class="flex flex-col gap-3">
            <div class="flex flex-col sm:flex-row gap-3">
                <input
                    type="search"
                    name="search"
                    value="{{ old('search', $filters['search']) }}"
                    placeholder="Search by name…"
                    class="flex-1 px-4 py-2 rounded bg-zinc-800 text-zinc-100 placeholder-zinc-500 border focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors {{ $errors->has('search') ? 'border-red-500 focus:border-red-500' : 'border-zinc-700 focus:border-green-500' }}"
                >

                <select name="status" class="px-4 py-2 rounded bg-zinc-800 text-zinc-100 border border-zinc-700 focus:outline-none focus:border-green-500">
                    <option value="">All Statuses</option>
                    @foreach ($filterOptions['status'] ?? [] as $option)
                        <option value="{{ $option }}" @selected($filters['status'] === $option)>{{ $option }}</option>
                    @endforeach
                </select>

                <select name="gender" class="px-4 py-2 rounded bg-zinc-800 text-zinc-100 border border-zinc-700 focus:outline-none focus:border-green-500">
                    <option value="">All Genders</option>
                    @foreach ($filterOptions['gender'] ?? [] as $option)
                        <option value="{{ $option }}" @selected($filters['gender'] === $option)>{{ $option }}</option>
                    @endforeach
                </select>

                <select name="species" class="px-4 py-2 rounded bg-zinc-800 text-zinc-100 border border-zinc-700 focus:outline-none focus:border-green-500">
                    <option value="">All Species</option>
                    @foreach ($filterOptions['species'] ?? [] as $option)
                        <option value="{{ $option }}" @selected($filters['species'] === $option)>{{ $option }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-5 py-2 bg-green-500 hover:bg-green-400 text-zinc-900 font-semibold rounded transition-colors">
                    Filter
                </button>

                @if (array_filter($filters))
                    <a href="{{ url('/characters') }}" class="px-5 py-2 bg-zinc-700 hover:bg-zinc-600 text-zinc-200 rounded transition-colors text-center">
                        Clear
                    </a>
                @endif
            </div>

            @error('search')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
        </form>
    </div>

    {{-- Results count --}}
    @if (!empty($info))
        <p class="text-sm text-zinc-500 mb-6">{{ number_format($info['count']) }} characters found</p>
    @endif

    {{-- Character grid --}}
    @if (!empty($characters))
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-10">
            @foreach ($characters as $character)
                <a href="{{ url('/characters/' . $character['id']) }}"
                   class="group bg-zinc-900 border border-zinc-800 hover:border-green-500/50 rounded-lg overflow-hidden transition-all hover:shadow-lg hover:shadow-green-500/5">
                    <div class="img-skeleton aspect-square">
                        <img
                            src="{{ url('/images/character/' . $character['id']) }}"
                            alt="{{ $character['name'] }}"
                            loading="lazy"
                            style="opacity:0; transition:opacity .4s ease, transform .3s ease;"
                            class="w-full h-full object-cover group-hover:scale-105"
                            onload="this.style.opacity=1; this.parentElement.classList.add('loaded')"
                        >
                    </div>
                    <div class="p-3">
                        <h2 class="text-sm font-semibold text-zinc-100 group-hover:text-green-400 transition-colors truncate">
                            {{ $character['name'] }}
                        </h2>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span @class([
                                'w-2 h-2 rounded-full shrink-0',
                                'bg-green-400' => $character['status'] === 'Alive',
                                'bg-red-500'   => $character['status'] === 'Dead',
                                'bg-zinc-500'  => $character['status'] === 'unknown',
                            ])></span>
                            <span class="text-xs text-zinc-400">{{ $character['status'] }} — {{ $character['species'] }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <x-pagination
            :currentPage="$currentPage"
            :totalPages="$totalPages"
            :pageNumbers="$pageNumbers"
            :filterQuery="$filterQuery"
            baseUrl="/characters"
        />

    @else
        <div class="text-center py-20 text-zinc-500">
            <p class="text-lg mb-2">No characters found</p>
            <a href="{{ url('/characters') }}" class="text-sm text-green-500 hover:text-green-400 transition-colors">Clear filters</a>
        </div>
    @endif

@endsection
