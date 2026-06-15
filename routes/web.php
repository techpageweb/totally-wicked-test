<?php

use App\Http\Controllers\CharacterController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::get('/images/character/{id}', [ImageController::class, 'characterAvatar'])->whereNumber('id');

Route::get('/characters', [CharacterController::class, 'index']);
Route::get('/characters/{id}', [CharacterController::class, 'show'])->whereNumber('id');

Route::get('/episodes', [EpisodeController::class, 'index']);
Route::get('/episodes/{id}', [EpisodeController::class, 'show'])->whereNumber('id');

Route::get('/locations', [LocationController::class, 'index']);
Route::get('/locations/{id}', [LocationController::class, 'show'])->whereNumber('id');
