<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $t) {
            $t->id();
            $t->string('source')->nullable();        // z ?src=
            $t->string('locale', 5);
            $t->string('company_name');
            $t->string('contact_name');
            $t->string('email');
            $t->string('phone')->nullable();
            $t->string('city')->nullable();
            $t->string('country', 2)->nullable();
            $t->text('message')->nullable();
            $t->json('utm')->nullable();
            $t->string('ip_hash', 64);               // sha256 + pepper, nigdy surowe IP
            $t->string('user_agent')->nullable();
            $t->enum('status', ['new', 'contacted', 'qualified', 'rejected'])
                ->default('new')->index();
            $t->timestamps();
        });

        Schema::create('availability_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained();
            $t->string('locale', 5);
            $t->string('name');
            $t->string('email');
            $t->string('postal_code')->nullable();
            $t->text('message')->nullable();
            $t->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $t) {
            $t->id();
            $t->string('email')->unique();
            $t->string('locale', 5);
            $t->string('token', 64);
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamp('unsubscribed_at')->nullable();
            $t->string('consent_text_version');
            $t->string('ip_hash', 64);
            $t->timestamps();
        });

        Schema::create('consents', function (Blueprint $t) {
            $t->id();
            $t->string('subject_type');
            $t->unsignedBigInteger('subject_id');
            $t->enum('type', ['newsletter', 'testimonial', 'photo', 'data_processing']);
            $t->boolean('granted');
            $t->timestamp('granted_at')->nullable();
            $t->timestamp('revoked_at')->nullable();
            $t->string('document_version');
            $t->json('evidence')->nullable();
            $t->timestamps();
            $t->index(['subject_type', 'subject_id']);
        });

        Schema::create('partner_stories', function (Blueprint $t) {
            $t->id();
            $t->string('partner_name')->nullable();
            $t->string('city')->nullable();
            $t->string('country', 2)->nullable();
            $t->string('locale', 5);
            $t->text('body');
            $t->foreignId('consent_id')->nullable()->constrained();
            $t->boolean('is_active')->default(true);
            $t->unsignedTinyInteger('position')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_stories');
        Schema::dropIfExists('consents');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('availability_requests');
        Schema::dropIfExists('leads');
    }
};
