<?php

namespace App\Http\Requests;

use App\Enums\InventoryType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class ProductInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(InventoryType::class)],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('Validate product inventory failed', [
            'errors' => $validator->errors()->all(),
            'input' => $this->all(),
            'url' => request()->fullUrl(),
        ]);

        $this->flash();
        session()->flash('dataError', 'Không thành công');

        throw new ValidationException($validator);
    }
}
