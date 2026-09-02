<?php

declare(strict_types=1);

use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Web\NewsletterController;
use App\Http\Controllers\Web\ProductController;
use Illuminate\Support\Facades\Route;

// §8.1 — struktura URL. x-default = de (rynek główny).
Route::redirect('/', '/de/');

// Newsletter B2C (dok. 07, double opt-in). Poza prefiksem locale, żeby nie
// kolidować z katalogiem /{locale}/... — throttling chroni endpoint zapisu.
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:10,1')
    ->name('newsletter.subscribe');
Route::get('/newsletter/confirm/{token}', [NewsletterController::class, 'confirm'])
    ->middleware('throttle:30,1')
    ->name('newsletter.confirm');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])
    ->middleware('throttle:30,1')
    ->name('newsletter.unsubscribe');

Route::get('/{locale}/', function (string $locale) {
    abort_unless(in_array($locale, config('evastone.locales'), true), 404);

    $products = Product::with(['translations', 'category.translations', 'stone.translations', 'media'])
        ->where('status', 'published')
        ->orderBy('model_no')
        ->get();

    $section = config("evastone.catalog_sections.{$locale}");

    return view('catalog.index', compact('products', 'locale', 'section'));
})->where('locale', 'de|en|pl');

Route::get('/{locale}/{section}/{category}/{slug}/', [ProductController::class, 'show'])
    ->where('locale', 'de|en|pl');
