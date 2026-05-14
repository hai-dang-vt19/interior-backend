<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAdminOrderItemVariants;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class OrderEditRequest extends FormRequest
{
    use ValidatesAdminOrderItemVariants;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'shipping_address' => ['required', 'string'],
            'shipping_phone' => ['required', 'string', 'max:20'],
            'shipping_provider' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'shipped_at' => ['nullable', 'date'],
            'delivered_at' => ['nullable', 'date'],
            'status' => ['required', new Enum(OrderStatus::class)],
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
            'payment_status' => ['required', new Enum(PaymentStatus::class)],
            'notes' => ['nullable', 'string'],
        ];

        if (Auth::user()?->role === UserRole::ADMIN) {
            $rules['order_items'] = ['required', 'array', 'min:1'];
            $rules['order_items.*.product_id'] = ['required', 'integer', 'exists:products,id'];
            $rules['order_items.*.product_variant_id'] = ['nullable', 'integer'];
            $rules['order_items.*.quantity'] = ['required', 'integer', 'min:1'];
        } else {
            $rules['order_items'] = ['prohibited'];
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('Validate order update failed', [
            'errors' => $validator->errors()->all(),
            'input' => $this->all(),
            'url' => request()->fullUrl(),
        ]);

        $this->flash();
        session()->flash('dataError', 'Không thành công');

        throw new ValidationException($validator);
    }
}
