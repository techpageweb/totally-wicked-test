# Rick and Morty Encyclopedia

A web-based encyclopedia built on the [Rick and Morty REST API](https://rickandmortyapi.com). Browse characters, episodes, and locations with search, filtering, and pagination throughout.

Built with Laravel 13, Blade, and Tailwind CSS v4.

---

## Requirements

- **PHP 8.3 or higher** (8.4 recommended) with the following extensions: `ext-curl`, `ext-mbstring`, `ext-xml`, `ext-sqlite3`
- **Composer** 2.x
- **Node.js** 18+ and **npm**
- Git

If you are using [Laravel Herd](https://herd.laravel.com) on macOS or Windows it handles PHP and the required extensions for you.

---

## Installation

```bash
git clone https://github.com/techpageweb/totally-wicked-test
cd totally-wicked-test

composer install

cp .env.example .env
php artisan key:generate

npm install
npm run build
```

No database setup is required — the app uses SQLite by default and only uses the filesystem for the cache and image storage.

### Storage permissions

On Linux/macOS, make sure the storage directory is writable:

```bash
chmod -R 775 storage bootstrap/cache
```

On Windows with Herd this is handled automatically.

---

## Running the App

```bash
php artisan serve
```

Then open [http://localhost:8000](http://localhost:8000).

For active frontend development with hot reloading, run Vite alongside the server:

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

---

## Usage

| Page | URL | Description |
|---|---|---|
| Home | `/` | Landing page with navigation |
| Characters | `/characters` | Browse all characters with search and filters |
| Character detail | `/characters/{id}` | Full profile, stats, and episode appearances |
| Episodes | `/episodes` | Browse all episodes, searchable by name or code |
| Episode detail | `/episodes/{id}` | Episode info and paginated character list |
| Locations | `/locations` | Browse all locations with type and dimension filters |
| Location detail | `/locations/{id}` | Location info and paginated resident list |

### Filters

- **Characters** — filter by status (Alive/Dead/unknown), gender, and species. All options are pulled from the API and cached for 24 hours, so the dropdowns always reflect the actual data.
- **Locations** — filter by type and dimension, same approach.
- **Episodes** — search by name or episode code (e.g. `S01E04`).

### API rate limiting

The app caches all API responses for 1 hour. If the API rate limit is hit before the cache is warm, a banner is shown on the affected page. Refresh after a moment and the cached data will serve the page without hitting the API again.

---

## Running Tests

```bash
# Run all tests
php artisan test

# Verbose output with test names
php vendor/phpunit/phpunit/phpunit --testdox

# Unit tests only
php artisan test --testsuite=Unit

# Single test file
php artisan test tests/Unit/Services/CharactersServiceTest.php
```

Tests cover the `RickAndMortyService` for characters, episodes, and locations using `Http::fake()` — no real API calls are made.

---

## Common Issues

**Blank page with no styles**

The frontend assets haven't been built. Run `npm run build` and reload.

---

**`Class "App\Http\Controllers\..." not found` or similar autoload errors**

Regenerate the Composer autoloader:

```bash
composer dump-autoload
```

---

**Wrong PHP version (`php -v` shows 7.x or 8.0/8.1)**

Your system PHP is taking priority over Herd. On Windows, make sure Herd's PHP is first in your `PATH`. You can check which binary is active with:

```bash
where php   # Windows
which php   # macOS/Linux
```

On macOS with Herd, run `herd php` instead of `php` if the path isn't updated, or follow the [Herd path setup docs](https://herd.laravel.com/docs).

---

**`Permission denied` writing to storage**

```bash
chmod -R 775 storage bootstrap/cache
```

If you're on Windows and using Herd, restart Herd — it occasionally loses write access after a sleep/wake cycle.

---

**Images not loading / showing broken on first visit**

Character images are proxied and cached locally on first request. If the API is rate limited at the point a new image is first fetched, it will 502. Refresh the page — the next request will retry. Once an image is cached it is served from disk indefinitely.

---

**`php artisan key:generate` says key already set**

That's fine — the app key is already in your `.env`. You only need to run it once after copying `.env.example`.

---

**Port 8000 already in use**

Run the server on a different port:

```bash
php artisan serve --port=8080
```

---

**Stale filter options in dropdowns**

Filter options (character species/gender/status, location type/dimension) are cached for 24 hours. To force a refresh:

```bash
php artisan cache:clear
```

---

## Development Notes

### Framework — Laravel

Laravel was chosen for a PHP assessment focused on MVC, OOP, and API integration. The codebase is clean and easy to follow with lots of different frontend options.

A few things made it more suited:

- The built-in HTTP client (a thin wrapper around Guzzle) handles API calls cleanly
- Laravel's cache layer is straightforward to drop in front of API calls, which solves the rate limiting problem
- Routing, validation, and error handling are all built in and easy to use

It's also a modern framework I have experience in.

---

### Templating — Blade

Blade is Laravel's native templating engine. It compiles down to plain PHP, which keeps things fast, and the template inheritance model (`@extends`, `@section`, `@yield`) makes it straightforward to build a consistent layout without repeating yourself across views.

I would normally use React or Vue for the frontend but Blade fits the task better.

---

### Image Loading — Proxy & Cache

Character images are served from `rickandmortyapi.com`. On the listing page that means up to 20 simultaneous image requests going to the same domain as the API calls, which quickly triggers rate limiting and causes images to fail.

To fix this, images are proxied through a Laravel route (`/images/character/{id}`). On first request the controller fetches the image from the API and writes it to local storage. Every request after that is served straight from disk — the API is never touched again for that image. A one-year `Cache-Control` header is also sent so the browser caches it client-side too.

The visual side of this is handled with a CSS shimmer skeleton while the image loads and a CSS opacity fade-in on load. The transition uses an inline `style="opacity:0"` rather than a Tailwind class because the class-removal approach can happen in the same browser paint cycle as the element appearing, leaving nothing to transition from. Inline style is parsed before the image loads, so the starting value is always set correctly.

#### Production consideration — S3 + CloudFront

Local storage works for a single-server test environment but breaks in production because multiple app servers each build their own independent cache and there is no CDN edge caching.

Because `ImageController` already uses Laravel's `Storage` facade (`Storage::exists`, `Storage::put`, `Storage::get`), switching to S3 is a config change rather than a code change:

```env
FILESYSTEM_DISK=s3
AWS_BUCKET=your-bucket
AWS_DEFAULT_REGION=eu-west-1
```
