<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AffiliateSignupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => preg_replace('/[\s\-()]+/', '', (string) $this->input('phone')),
            'whatsapp_number' => $this->filled('whatsapp_number')
                ? preg_replace('/[\s\-()]+/', '', (string) $this->input('whatsapp_number'))
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^(\+?234|0)[789]\d{9}$/'],
            'whatsapp_number' => ['nullable', 'string', 'regex:/^(\+?234|0)[789]\d{9}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'social_handles' => ['nullable', 'array'],
            'social_handles.instagram' => ['nullable', 'string', 'max:100'],
            'social_handles.twitter' => ['nullable', 'string', 'max:100'],
            'social_handles.tiktok' => ['nullable', 'string', 'max:100'],
            'social_handles.facebook' => ['nullable', 'string', 'max:100'],
            'agreed_to_terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid Nigerian phone number.',
            'whatsapp_number.regex' => 'Enter a valid Nigerian WhatsApp number.',
            'agreed_to_terms.accepted' => 'You must agree to the affiliate terms to continue.',
        ];
    }
}
