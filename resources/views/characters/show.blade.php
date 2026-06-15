@extends('layouts.app')

@section('title', $character['name'] . ' — ' . config('app.name'))
@section('meta_description', $character['name'] . ' is a ' . $character['status'] . ' ' . $character['species'] . ' in Rick and Morty.')

@section('breadcrumbs')
    <a href="{{ url('/characters') }}" class="hover:text-green-400 transition-colors">Characters</a>
    <span class="mx-2">/</span>
    <span class="text-zinc-300">{{ $character['name'] }}</span>
@endsection

@section('content')

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full">

        {{-- Image --}}
        <div class="md:col-span-1">
            <div class="img-skeleton rounded-xl border border-zinc-800 sticky top-6">
                <img
                    src="{{ url('/images/character/' . $character['id']) }}"
                    alt="{{ $character['name'] }}"
                    style="opacity:0; transition:opacity .6s ease; display:block; width:100%;"
                    class="object-cover"
                    onload="this.style.opacity=1; this.parentElement.classList.add('loaded')"
                >
            </div>
        </div>

        {{-- Details --}}
        <div class="md:col-span-2 space-y-6 w-full">

            {{-- Name & status --}}
            <div>
                <h1 class="text-3xl font-bold text-zinc-100">{{ $character['name'] }}</h1>
                <div class="flex items-center gap-3 my-2">
                    <span @class([
                        'w-3 h-3 rounded-full shrink-0',
                        'bg-green-400' => $character['status'] === 'Alive',
                        'bg-red-500'   => $character['status'] === 'Dead',
                        'bg-zinc-500'  => $character['status'] === 'unknown',
                    ])></span>
                    <span class="text-sm text-zinc-400 ">{{ $character['status'] }}</span>
                </div>
            </div>

            {{-- Info grid --}}
            <div class="grid grid-cols-2 gap-4 w-full">
                <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                    <p class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Species</p>
                    <p class="text-zinc-100 font-medium">{{ $character['species'] }}</p>
                </div>

                <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                    <p class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Gender</p>
                    <p class="text-zinc-100 font-medium">{{ $character['gender'] }}</p>
                </div>

                @if ($character['type'])
                    <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                        <p class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Type</p>
                        <p class="text-zinc-100 font-medium">{{ $character['type'] }}</p>
                    </div>
                @endif

                <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                    <p class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Origin</p>
                    <p class="text-zinc-100 font-medium">{{ $character['origin']['name'] }}</p>
                </div>

                <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                    <p class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Last Known Location</p>
                    <p class="text-zinc-100 font-medium">{{ $character['location']['name'] }}</p>
                </div>

                <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                    <p class="text-xs text-zinc-500 uppercase tracking-wide mb-1">Episodes</p>
                    <p class="text-zinc-100 font-medium">{{ count($character['episode']) }}</p>
                </div>
            </div>

            {{-- Episodes --}}
            @if (!empty($episodes))
                <div>
                    <h2 class="text-lg font-semibold text-zinc-100 mb-3">Episode Appearances</h2>
                    <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                        @foreach ($episodes as $episode)
                            <div class="flex items-center justify-between bg-zinc-900 border border-zinc-800 rounded-lg px-4 py-3">
                                <span class="text-sm text-zinc-100">{{ $episode['name'] }}</span>
                                <span class="text-xs text-green-400 font-mono shrink-0 ml-4">{{ $episode['episode'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Back link --}}
    <div class="mt-10">
        <a href="{{ url('/characters') }}" class="text-lg text-zinc-500 hover:text-green-400 transition-colors">
            &larr; Back to Characters
        </a>
    </div>

@endsection
