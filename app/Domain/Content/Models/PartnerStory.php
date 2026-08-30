<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use App\Domain\Consents\Models\Consent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerStory extends Model
{
    protected $fillable = [
        'partner_name', 'city', 'country', 'locale', 'body',
        'consent_id', 'is_active', 'position',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function consent(): BelongsTo
    {
        return $this->belongsTo(Consent::class);
    }

    /**
     * §3.4 / dok. 20 §1.3 — named/anonymous. Nazwisko tylko przy aktywnej
     * zgodzie (granted, nieodwołanej). Nigdy fikcyjne nazwisko.
     * Jedna metoda na modelu, nie warunek w Blade.
     */
    public function displayName(): string
    {
        $consentActive = $this->consent
            && $this->consent->granted
            && $this->consent->revoked_at === null;

        if ($this->partner_name && $consentActive) {
            return $this->partner_name;
        }

        return match ($this->locale) {
            'de' => 'ein Juwelier aus '.($this->city ?? 'der Region'),
            'en' => 'a jeweller from '.($this->city ?? 'the region'),
            default => 'jubiler z '.($this->city ?? 'regionu'),
        };
    }
}
