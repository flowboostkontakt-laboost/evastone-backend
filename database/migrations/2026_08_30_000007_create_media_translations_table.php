<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // §6.2 — alt-y opisowe per locale. Publikacja blokowana przy pustym alt.
        Schema::create('media_translations', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('media_id')->index(); // spatie medialibrary `media`
            $t->string('locale', 5);
            $t->string('alt', 500);
            $t->timestamps();
            $t->unique(['media_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_translations');
    }
};
