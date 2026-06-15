# Development Notes

## Setup

```bash
git clone <repo-url>
cd <repo-folder>

composer install
cp .env.example .env
php artisan key:generate

npm install
npm run build

php artisan serve
```

The app runs on SQLite by default — no database setup required.

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
