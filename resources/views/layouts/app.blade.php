<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'A Rick and Morty character encyclopedia.')">
    <title>@yield('title', config('app.name', 'Rick & Morty Encyclopedia'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen flex flex-col">

    {{-- Navigation --}}
    <header class="bg-zinc-900 border-b border-zinc-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center shrink-0 shadow-lg shadow-green-500/30">
                        <svg class="w-5 h-5 text-zinc-900" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="font-bold text-lg tracking-wide group-hover:text-green-400 transition-colors">
                        {{ config('app.name', 'Rick & Morty') }}
                    </span>
                </a>

                {{-- Primary nav --}}
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                    <a href="{{ url('/') }}" class="hover:text-green-400 transition-colors {{ request()->is('/') ? 'text-green-400' : 'text-zinc-300' }}">
                        Home
                    </a>
                    <a href="{{ url('/characters') }}" class="hover:text-green-400 transition-colors {{ request()->is('characters*') ? 'text-green-400' : 'text-zinc-300' }}">
                        Characters
                    </a>
                    <a href="{{ url('/locations') }}" class="hover:text-green-400 transition-colors {{ request()->is('locations*') ? 'text-green-400' : 'text-zinc-300' }}">
                        Locations
                    </a>
                    <a href="{{ url('/episodes') }}" class="hover:text-green-400 transition-colors {{ request()->is('episodes*') ? 'text-green-400' : 'text-zinc-300' }}">
                        Episodes
                    </a>
                    <a href="https://rickandmortyapi.com" target="_blank" rel="noopener" class="flex items-center gap-1.5 bg-green-500 hover:bg-green-400 text-zinc-900 font-semibold px-3 py-1.5 rounded transition-colors shadow shadow-green-500/20">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        API
                    </a>
                </nav>

                {{-- Mobile menu button --}}
                <button
                    type="button"
                    class="md:hidden p-2 rounded text-zinc-300 hover:text-green-400 hover:bg-zinc-800 transition-colors"
                    onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
                    aria-label="Toggle menu"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            {{-- Mobile menu --}}
            <div id="mobile-menu" class="hidden md:hidden pb-4 border-t border-zinc-800 mt-2 pt-3 flex flex-col gap-3 text-sm font-medium">
                <a href="{{ url('/') }}" class="text-zinc-300 hover:text-green-400 transition-colors">Home</a>
                <a href="{{ url('/characters') }}" class="text-zinc-300 hover:text-green-400 transition-colors">Characters</a>
                <a href="{{ url('/locations') }}" class="text-zinc-300 hover:text-green-400 transition-colors">Locations</a>
                <a href="{{ url('/episodes') }}" class="text-zinc-300 hover:text-green-400 transition-colors">Episodes</a>
                <a href="https://rickandmortyapi.com" target="_blank" rel="noopener" class="text-green-400 font-semibold">API</a>
            </div>
        </div>
    </header>

    {{-- hero slot --}}
    @hasSection('hero')
        @yield('hero')
    @endif

    {{-- Breadcrumbs --}}
    @hasSection('breadcrumbs')
        <div class="bg-zinc-900 border-b border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 text-sm text-zinc-500">
                @yield('breadcrumbs')
            </div>
        </div>
    @endif

    {{-- Main content --}}
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-zinc-900 border-t border-zinc-800 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-zinc-500">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Rick & Morty Encyclopedia') }}. Data from the <a href="https://rickandmortyapi.com" target="_blank" class="text-green-500 hover:text-green-400 transition-colors">Rick and Morty API</a>.</p>
                <a href="https://rickandmortyapi.com" target="_blank" rel="noopener" class="hover:text-green-400 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    API Reference
                </a>
            </div>
        </div>
    </footer>

</body>
</html>
