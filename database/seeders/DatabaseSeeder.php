<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // §4.1 krok 1: seed słowników PRZED wszystkim innym
        $this->call(DictionarySeeder::class);

        User::updateOrCreate(
            ['email' => 'admin@evastone.eu'],
            [
                'name' => 'Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ],
        );
    }
}
