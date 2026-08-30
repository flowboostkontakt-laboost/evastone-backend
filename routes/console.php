<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

// §14 — scheduler. stone:rotate / sitemap:generate / newsletter:purge /
// demo:cleanup dojdą w blokach 7–9 (patrz README, sekcja „Stan realizacji").
Schedule::command('comarch:sync')->everySixHours();
