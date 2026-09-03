<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Formularz B2B /wholesale (spec 16 §4.1). Wariant a5 = pełny, b3 = tylko
 * firma+website+email (pole `wariant` z data-wariant). Honeypot `firma_www`.
 */
class LeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $emailRule = app()->environment('testing') ? 'email:rfc' : 'email:rfc,dns';

        return [
            'firma' => ['required', 'string', 'max:200'],
            'email' => ['required', 'string', $emailRule, 'max:254'],
            'website' => ['nullable', 'string', 'max:200'],
            'person' => ['nullable', 'string', 'max:200'],
            'stadt' => ['nullable', 'string', 'max:120'],
            'land' => ['nullable', 'string', 'max:2'],
            'wariant' => ['nullable', Rule::in(['a5', 'b3'])],
            'src' => ['nullable', 'string', 'max:40'],
            // honeypot — wypełnione = bot
            'firma_www' => ['nullable', 'prohibited'],
        ];
    }
}
