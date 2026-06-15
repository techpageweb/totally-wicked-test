@extends('layouts.app')

@section('title', $location['name'] . ' — ' . config('app.name'))
@section('meta_description', $location['name'] . ' is a ' . $location['type'] . ' in ' . $location['dimension'] . '.')

@section('breadcrumbs')
    <a href="{{ url('/locations') }}" class="hover:text-green-400 transition-colors">Locations</a>
    <span class="mx-2">/</span>
    <span class="text-zinc-300">{{ $location['name'] }}</span>
@endsection

@section('content')

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-zinc-100 mb-4">{{ $location['name'] }}</h1>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 max-w-lg">
            @if ($location['type'] && $location['type'] !== 'unknown')
                <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                    <p class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Type</p>
                    <p class="text-zinc-100 font-medium">{{ $location['type'] }}</p>
                </div>
            @endif

            @if ($location['dimension'] && $location['dimension'] !== 'unknown')
                <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                    <p class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Dimension</p>
                    <p class="text-zinc-100 font-medium">{{ $location['dimension'] }}</p>
                </div>
            @endif

            <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                <p class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Residents</p>
                <p class="text-zinc-100 font-medium">{{ count($location['residents']) }}</p>
            </div>
        </div>
    </div>

    {{-- Residents grid --}}
    @if (count($location['residents']) === 0)
        <p class="text-zinc-500 mb-10">No known residents recorded for this location.</p>
    @else
        <h2 class="text-lg font-semibold text-zinc-100 mb-4">
            Known Residents
            <span class="text-sm font-normal text-zinc-500">({{ count($location['residents']) }} total)</span>
        </h2>

        @if (!empty($residents))
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
                @foreach ($residents as $character)
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
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span @class([
                                    'w-2 h-2 rounded-full shrink-0',
                                    'bg-green-400' => $character['status'] === 'Alive',
                                    'bg-red-500'   => $character['status'] === 'Dead',
                                    'bg-zinc-500'  => $character['status'] === 'unknown',
                                ])></span>
                                <p class="text-xs text-zinc-500 truncate">{{ $character['status'] }}</p>
                            </div>
                        </div>

                    </a>
                @endforeach
            </div>

            <x-pagination
                :currentPage="$currentPage"
                :totalPages="$totalPages"
                :pageNumbers="$pageNumbers"
                filterQuery=""
                :baseUrl="'/locations/' . $location['id']"
            />
        @endif
    @endif

    {{-- Back link --}}
    <div class="mt-4">
        <a href="{{ url('/locations') }}" class="text-lg text-zinc-500 hover:text-green-400 transition-colors">
            &larr; Back to Locations
        </a>
    </div>

@endsection
