@props(['rateLimited' => false])

@if ($rateLimited)
    <div class="mb-6 flex items-start gap-3 rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-400">
        <span class="mt-0.5 shrink-0">&#9888;</span>
        <span>The API rate limit has been reached. Please wait a moment and <a href="{{ request()->fullUrl() }}" class="underline hover:text-red-300">try again</a>.</span>
    </div>
@endif
