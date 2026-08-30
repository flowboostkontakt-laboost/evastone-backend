<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();          // klucz techniczny, nie tłumaczony
            $t->unsignedTinyInteger('position');
            $t->timestamps();
        });

        Schema::create('category_translations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->string('locale', 5);
            $t->string('name');
            $t->string('slug_localized');           // /de/schmuck/ringe
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->text('answer_summary')->nullable();
            $t->unique(['category_id', 'locale']);
            $t->unique(['locale', 'slug_localized']);
        });

        Schema::create('stones', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->unsignedTinyInteger('position');
            $t->timestamps();
        });

        Schema::create('stone_translations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('stone_id')->constrained()->cascadeOnDelete();
            $t->string('locale', 5);
            $t->string('name');
            $t->string('slug_localized');
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->text('answer_summary')->nullable();
            $t->json('faq')->nullable();
            $t->unique(['stone_id', 'locale']);
            $t->unique(['locale', 'slug_localized']);
        });

        Schema::create('cuts', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->unsignedTinyInteger('position');
            $t->timestamps();
        });

        Schema::create('cut_translations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cut_id')->constrained()->cascadeOnDelete();
            $t->string('locale', 5);
            $t->string('name');
            $t->string('slug_localized');
            $t->unique(['cut_id', 'locale']);
            $t->unique(['locale', 'slug_localized']);
        });

        Schema::create('collections', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->unsignedTinyInteger('position');
            $t->timestamps();
        });

        Schema::create('collection_translations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $t->string('locale', 5);
            $t->string('name');
            $t->unique(['collection_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_translations');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('cut_translations');
        Schema::dropIfExists('cuts');
        Schema::dropIfExists('stone_translations');
        Schema::dropIfExists('stones');
        Schema::dropIfExists('category_translations');
        Schema::dropIfExists('categories');
    }
};
