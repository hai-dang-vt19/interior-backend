<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CustomerContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('Validate customer contact failed', [
            'errors' => $validator->errors()->all(),
            'input' => $this->all(),
            'url' => request()->fullUrl(),
        ]);

        throw new ValidationException($validator);
    }
}
