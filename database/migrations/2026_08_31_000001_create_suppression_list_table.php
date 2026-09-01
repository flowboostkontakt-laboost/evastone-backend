<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dok. 07 §1/§6 — globalny rejestr sprzeciwów działający W POPRZEK kanałów.
 *
 * Twarda blokada techniczna (nie procedura): zbiór S-CD (>7000 kontaktów
 * z płyty) oraz każdy adres po rezygnacji/sprzeciwie/skardze/twardym
 * odbiciu NIE MOŻE trafić do żadnej wysyłki. Klucz dopasowania to hash
 * adresu (sha256 + pepper) — surowego maila nie trzymamy (spójnie z ip_hash).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppression_list', function (Blueprint $t) {
            $t->id();
            $t->string('email_hash', 64)->unique();   // sha256(lower(email)+pepper)
            $t->enum('reason', ['s_cd', 's_own_reject', 'unsubscribe', 'objection', 'bounce', 'complaint']);
            $t->string('channel')->nullable();         // newsletter/email/… (sprzeciw w poprzek kanałów)
            $t->string('note')->nullable();
            $t->timestamps();
            $t->index('reason');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppression_list');
    }
};
