<?php

declare(strict_types=1);

// §8.1 — bezprefiksowy URL przekierowuje na locale x-default (de).
test('root redirects to the default locale', function () {
    $this->get('/')->assertRedirect('/de/');
});
