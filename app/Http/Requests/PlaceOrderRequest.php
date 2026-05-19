<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('customer_phone')) {
            $this->merge([
                'customer_phone' => preg_replace('/[\s\-()]+/', '', (string) $this->input('customer_phone')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^(\+?234|0)[789]\d{9}$/'],
            'delivery_method' => ['required', Rule::in(['delivery', 'pickup'])],
            'delivery_state' => ['required', 'string', 'max:255'],
            'delivery_lga' => ['required', 'string', 'max:255'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
            'payment_gateway' => ['required', Rule::in(Order::GATEWAYS)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Enter a valid Nigerian phone number.',
        ];
    }
}
