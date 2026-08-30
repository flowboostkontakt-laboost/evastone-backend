<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $t) {
            $t->id();
            $t->string('uid')->unique();               // w URL, NIE sekwencyjny (ULID/base32)
            $t->foreignId('product_id')->constrained();
            $t->string('serial_no');                   // „Egzemplarz nr 7"
            $t->string('workshop', 10);                // P-1 / P-2, bez nazwy wewnętrznej
            $t->date('issued_at');
            $t->unsignedTinyInteger('level');          // 1 = NTAG 213/216, 2 = 424 DNA
            $t->timestamp('last_verified_at')->nullable();
            $t->boolean('revoked')->default(false);
            $t->boolean('is_demo')->default(false);    // watermark „Wzór certyfikatu"
            $t->timestamps();
            $t->unique(['product_id', 'serial_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
