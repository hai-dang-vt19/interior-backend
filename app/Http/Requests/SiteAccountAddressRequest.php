<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SiteAccountAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_line' => ['required', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'address_line' => 'địa chỉ cụ thể',
            'ward' => 'phường/xã',
            'district' => 'quận/huyện',
            'city' => 'tỉnh/thành phố',
        ];
    }
}
