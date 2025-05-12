<?php

namespace App\Http\Requests;

use App\Enums\CustomerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string'],
            'phone' => [
                'required',
                'string',
                // 'regex:/^[0-9]{10,11}$/'
            ],
            'status' => ['required', new Enum(CustomerStatus::class)]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('Validate customer faild', [
            'errors' => $validator->errors()->all(),
            'input' => $this->all(),
            'url' => request()->fullUrl(),
        ]);

        $this->flash();
        session()->flash('dataError', 'Không thành công');

        throw new ValidationException($validator);
    }
}
