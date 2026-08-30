<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Unit');

/** Rekurencyjna asercja braku klucza w strukturze schema.org. */
function assertArrayNeverHasKey(array $arr, string $key): void
{
    expect(array_key_exists($key, $arr))->toBeFalse("Struktura zawiera zakazany klucz: {$key}");
    foreach ($arr as $value) {
        if (is_array($value)) {
            assertArrayNeverHasKey($value, $key);
        }
    }
}
