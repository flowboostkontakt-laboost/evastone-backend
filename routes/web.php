<?php

declare(strict_types=1);

use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Web\ProductController;
use Illuminate\Support\Facades\Route;

// §8.1 — struktura URL. x-default = de (rynek główny).
Route::redirect('/', '/de/');

Route::get('/{locale}/', function (string $locale) {
    abort_unless(in_array($locale, config('evastone.locales'), true), 404);

    $products = Product::with(['translations', 'category.translations'])
        ->where('status', 'published')
        ->orderBy('model_no')
        ->get();

    $section = config("evastone.catalog_sections.{$locale}");

    return view('catalog.index', compact('products', 'locale', 'section'));
})->where('locale', 'de|en|pl');

Route::get('/{locale}/{section}/{category}/{slug}/', [ProductController::class, 'show'])
    ->where('locale', 'de|en|pl');
