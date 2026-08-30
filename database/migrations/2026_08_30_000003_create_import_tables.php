<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_products_raw', function (Blueprint $t) {
            $t->id();
            $t->string('batch_id', 26);              // ULID przebiegu
            $t->string('source', 20);                // 'comup'
            $t->string('external_id')->nullable();
            $t->json('payload');                     // surowy rekord, BEZ czyszczenia
            $t->json('normalized')->nullable();      // wynik import:normalize — payload zostaje surowy
            $t->string('row_hash', 64)->index();     // idempotencja
            $t->enum('state', ['raw', 'normalized', 'rejected', 'imported'])->default('raw');
            $t->json('errors')->nullable();
            $t->timestamps();
            $t->unique(['source', 'row_hash']);
        });

        Schema::create('import_runs', function (Blueprint $t) {
            $t->id();
            $t->string('batch_id', 26)->index();
            $t->string('command');
            $t->json('stats')->nullable();
            $t->enum('status', ['running', 'finished', 'failed'])->default('running');
            $t->timestamp('started_at');
            $t->timestamp('finished_at')->nullable();
            $t->timestamps();
        });

        Schema::create('comarch_discrepancies', function (Blueprint $t) {
            $t->id();
            $t->string('model_no')->index();
            $t->string('field');
            $t->text('comup_value')->nullable();
            $t->text('comarch_value')->nullable();
            $t->string('batch_id', 26)->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comarch_discrepancies');
        Schema::dropIfExists('import_runs');
        Schema::dropIfExists('import_products_raw');
    }
};
