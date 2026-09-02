<?php

declare(strict_types=1);

use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\NewsletterController;
use App\Http\Controllers\Web\ProductController;
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

// /{locale}/ → katalog sekcji (front landing na razie = katalog)
Route::get('/{locale}/', function (string $locale) {
    abort_unless(in_array($locale, config('evastone.locales'), true), 404);
    return redirect('/'.$locale.'/'.config("evastone.catalog_sections.{$locale}").'/');
})->where('locale', 'de|en|pl');

// katalog: wszystkie produkty i per kategoria
Route::get('/{locale}/{section}/', [CatalogController::class, 'index'])->where('locale', 'de|en|pl');
Route::get('/{locale}/{section}/{category}/', [CatalogController::class, 'index'])->where('locale', 'de|en|pl');

// strona produktu
Route::get('/{locale}/{section}/{category}/{slug}/', [ProductController::class, 'show'])->where('locale', 'de|en|pl');
