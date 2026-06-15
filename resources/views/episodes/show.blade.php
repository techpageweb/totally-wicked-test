@extends('layouts.app')

@section('title', $episode['name'] . ' — ' . config('app.name'))
@section('meta_description', $episode['name'] . ' (' . $episode['episode'] . ') aired ' . $episode['air_date'] . '.')

@section('breadcrumbs')
    <a href="{{ url('/episodes') }}" class="hover:text-green-400 transition-colors">Episodes</a>
    <span class="mx-2">/</span>
    <span class="text-zinc-300">{{ $episode['name'] }}</span>
@endsection

@section('content')

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex flex-wrap items-center gap-3 mb-2">
            <span class="text-xs font-mono text-green-400 bg-green-500/10 border border-green-500/20 rounded px-3 py-1">
                {{ $episode['episode'] }}
            </span>
        </div>
        <h1 class="text-3xl font-bold text-zinc-100 mb-4">{{ $episode['name'] }}</h1>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 max-w-lg">
            <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                <p class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Air Date</p>
                <p class="text-zinc-100 font-medium">{{ $episode['air_date'] }}</p>
            </div>

            <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                <p class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Season</p>
                <p class="text-zinc-100 font-medium">{{ (int) substr($episode['episode'], 1, 2) }}</p>
            </div>

            <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                <p class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Characters</p>
                <p class="text-zinc-100 font-medium">{{ count($episode['characters']) }}</p>
            </div>
        </div>
    </div>

    {{-- Character grid --}}
    @if (!empty($episode['characters']))
        <h2 class="text-lg font-semibold text-zinc-100 mb-4">
            Characters in this Episode
            <span class="text-sm font-normal text-zinc-500">({{ count($episode['characters']) }} total)</span>
        </h2>

        @if (!empty($characters))
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
                @foreach ($characters as $character)
                    <a href="{{ url('/characters/' . $character['id']) }}"
                       class="group bg-zinc-900 border border-zinc-800 hover:border-green-500/50 rounded-lg overflow-hidden transition-all hover:shadow-lg hover:shadow-green-500/5">

                        <div class="img-skeleton aspect-square">
                            <img
                                src="{{ url('/images/character/' . $character['id']) }}"
                                alt="{{ $character['name'] }}"
                                style="opacity:0; transition:opacity .5s ease; display:block; width:100%; height:100%; object-fit:cover;"
                                onload="this.style.opacity=1; this.parentElement.classList.add('loaded')"
                            >
                        </div>

                        <div class="p-3">
                            <p class="text-sm font-medium text-zinc-100 group-hover:text-green-400 transition-colors truncate">
                                {{ $character['name'] }}
                            </p>
                            <p class="text-xs text-zinc-500 mt-0.5 truncate">{{ $character['species'] }}</p>
                        </div>

                    </a>
                @endforeach
            </div>

            <x-pagination
                :currentPage="$currentPage"
                :totalPages="$totalPages"
                :pageNumbers="$pageNumbers"
                filterQuery=""
                :baseUrl="'/episodes/' . $episode['id']"
            />
        @endif
    @endif

    {{-- Back link --}}
    <div class="mt-4">
        <a href="{{ url('/episodes') }}" class="text-lg text-zinc-500 hover:text-green-400 transition-colors">
            &larr; Back to Episodes
        </a>
    </div>

@endsection
