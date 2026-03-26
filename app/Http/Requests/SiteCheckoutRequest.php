<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SiteCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address' => ['required', 'string'],
            'shipping_phone' => ['required', 'string', 'max:20'],
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
