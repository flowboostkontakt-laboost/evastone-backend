<?php

declare(strict_types=1);

use App\Http\Controllers\Web\NewsletterController;
use App\Http\Controllers\Web\PageController;
use Illuminate\Support\Facades\Route;

// §8.1 — struktura URL. x-default = de (rynek główny).
Route::redirect('/', '/de/');

// Newsletter B2C (dok. 07, double opt-in).
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:10,1')->name('newsletter.subscribe');
Route::get('/newsletter/confirm/{token}', [NewsletterController::class, 'confirm'])
    ->middleware('throttle:30,1')->name('newsletter.confirm');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])
    ->middleware('throttle:30,1')->name('newsletter.unsubscribe');

// Dyspozytor: strona główna, katalog (dynamiczny), podstrony (statyczne).
Route::get('/{locale}', [PageController::class, 'handle'])->where('locale', 'de|en|pl');
Route::get('/{locale}/{path}', [PageController::class, 'handle'])
    ->where(['locale' => 'de|en|pl', 'path' => '.*']);
