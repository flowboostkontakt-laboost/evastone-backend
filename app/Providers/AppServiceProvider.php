<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Render/proxy kończy TLS i przekazuje http do apki → Laravel generuje
        // linki http (mixed content na https, blokada CSS/JS Filamenta).
        // Wymuszamy https dla wszystkich generowanych URL-i na produkcji.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
