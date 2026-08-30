<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // KONTRAKT §3.2 i §3.3: brak jakiegokolwiek pola `price` w modelu products.
        // Ceny istnieją wyłącznie w tenancie demo (tabela demo_prices).
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('model_no')->unique();          // KLUCZ BIZNESOWY — z Comarcha
            $t->foreignId('category_id')->constrained();
            $t->foreignId('stone_id')->nullable()->constrained();
            $t->foreignId('cut_id')->nullable()->constrained();
            $t->foreignId('collection_id')->nullable()->constrained();
            $t->json('spec');
            $t->json('sizes')->nullable();             // rozmiarówka z Comarcha
            $t->string('care_ref')->nullable();        // link do filaru P5
            $t->boolean('is_bestseller')->default(false);
            $t->unsignedInteger('bestseller_rank')->nullable();
            $t->enum('status', ['draft', 'needs_review', 'published', 'archived'])
                ->default('draft')->index();
            $t->timestamp('published_at')->nullable();
            $t->string('source_hash', 64)->nullable(); // idempotencja importu
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('product_translations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('locale', 5);
            $t->string('name');
            $t->string('slug_localized');
            $t->text('answer_summary')->nullable();    // 40–60 słów, AEO
            $t->longText('description')->nullable();
            $t->json('faq')->nullable();               // [{q,a}] 2–4 pary
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->enum('review_status', ['missing', 'draft', 'client_review', 'approved'])
                ->default('missing')->index();
            $t->timestamp('approved_at')->nullable();
            $t->string('approved_by')->nullable();     // log kto zatwierdził (§7.3)
            $t->unique(['product_id', 'locale']);
            $t->unique(['locale', 'slug_localized']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
    }
};
