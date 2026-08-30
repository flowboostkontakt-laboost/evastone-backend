<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // §12 — sklep demo. Model danych pod stancl/tenancy (single-database).
        Schema::create('tenants', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('domain')->nullable()->unique();
            $t->boolean('is_demo')->default(false);
            $t->boolean('catalog_mode')->default(true);
            $t->unsignedSmallInteger('featured_count')->default(12);
            $t->json('brand')->nullable();             // logo, accent_color, name
            $t->timestamps();
        });

        // §12.3 — ceny WYŁĄCZNIE tutaj. Generowane regułą (config/demo_pricing.php),
        // parametry reguły w .env (poufne), nigdy w repozytorium.
        Schema::create('demo_prices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('amount');             // końcówki zawsze = 0
            $t->char('currency', 3)->default('EUR');
            $t->timestamps();
            $t->unique(['tenant_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_prices');
        Schema::dropIfExists('tenants');
    }
};
