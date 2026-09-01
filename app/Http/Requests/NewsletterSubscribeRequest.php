<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewsletterSubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // DNS-check odsiewa literówki domen w produkcji; w testach offline
        // sam RFC, żeby zestaw był hermetyczny.
        $emailRule = app()->environment('testing') ? 'email:rfc' : 'email:rfc,dns';

        return [
            'email' => ['required', 'string', $emailRule, 'max:254'],
            'locale' => ['required', Rule::in(config('evastone.locales'))],
            // dok. 07: zgoda musi być wyrażona świadomie (checkbox), nie domyślna
            'consent' => ['accepted'],
            // honeypot — boty wypełnią; ludzie nie widzą pola
            'website' => ['nullable', 'prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent.accepted' => 'Wymagana jest zgoda na otrzymywanie newslettera.',
        ];
    }
}
