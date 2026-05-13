<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'payment_method' => [
                'required',
                Rule::in(array_map(static fn (PaymentMethod $m) => $m->value, PaymentMethod::forSiteCheckout())),
            ],
            'notes' => ['nullable', 'string'],
            'selected_items' => ['nullable', 'string'],
        ];
    }
}
