<?php

namespace App\Http\Requests;

use App\Http\Controllers\CartController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cartable_type' => ['required', 'string', Rule::in(array_keys(CartController::CARTABLE_TYPES))],
            'cartable_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }
}
