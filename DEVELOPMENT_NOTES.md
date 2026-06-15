# Development Notes

## Setup

```bash
git clone https://github.com/techpageweb/totally-wicked-test
cd <repo-folder>

composer install
cp .env.example .env
php artisan key:generate

npm install
npm run build

php artisan serve
```

The app runs on SQLite by default — no database setup required.

## Running Tests
Tests are created with the assistance of AI (Claude skill)

```bash
# Run all tests
php artisan test

# Run with descriptive test names
php vendor/phpunit/phpunit/phpunit --testdox

# Run only unit tests
php artisan test --testsuite=Unit

# Run a specific file
php artisan test tests/Unit/Services/RickAndMortyServiceTest.php
```

---

## Framework — Laravel

Laravel was chosen for a PHP assessment focused on MVC, OOP, and API integration. The codebase clean and easy to follow with lots of different frontend options.

A few things made it more suited:

- The built-in HTTP client (a thin wrapper around Guzzle) which will handle API calls cleanly
- Laravel's cache layer is straightforward to drop in front of API calls, which solves the "API is rate-limited" issue
- Routing, validation, and error handling are all built in and easy to use

It's also a modern framework I have experience in

---

## Templating — Blade

Blade is Laravel's native templating engine. It compiles down to plain PHP, which keeps things fast, and the template inheritance model (`@extends`, `@section`, `@yield`) makes it straightforward to build a consistent layout without repeating yourself across views.

I would normally use React or Vue frontend methods but Blade fits the task better

---

## Image Loading — Proxy & Cache

Character images are served from `rickandmortyapi.com`. On the listing page that means up to 20 simultaneous image requests going to the same domain as the API calls, which quickly triggers rate limiting and causes images to fail to load.

To fix this, images are proxied through a Laravel route (`/images/character/{id}`). On first request the controller fetches the image from the API and writes it to local storage. Every request after that is served straight from disk — the API is never touched again for that image. A one-year `Cache-Control` header is also sent so the browser caches it client-side too.

The visual side of this is handled with a CSS shimmer skeleton while the image loads and a CSS opacity fade-in on load. The transition uses an inline `style="opacity:0"` rather than a Tailwind class because the class-removal approach can happen in the same browser paint cycle as the element appearing, leaving nothing to transition from. Inline style is parsed before the image loads, so the starting value is always set correctly.

### Production consideration — S3 + CloudFront

Local storage works for a single-server test environment but breaks in production because: multiple app servers each build their own independent cache and there is no CDN edge caching so all files are served from one region.

S3 + CloudFront could be used. Because `ImageController` already uses Laravel's `Storage` facade (`Storage::exists`, `Storage::put`, `Storage::get`), switching is a config change rather than a code change:

```env
FILESYSTEM_DISK=s3
AWS_BUCKET=your-bucket
AWS_DEFAULT_REGION=eu-west-1
```
