<?php

namespace App\Http\Requests;

use App\Enums\InventoryType;
use App\Models\ProductVariant;
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
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $productId = (int) $this->route('id');

            $variantRows = ProductVariant::query()->where('product_id', $productId)->count();
            if ($variantRows < 1) {
                return;
            }

            $vid = $this->input('product_variant_id');
            if ($vid === null || $vid === '') {
                $v->errors()->add('product_variant_id', 'Sản phẩm có phiên bản — vui lòng chọn phiên bản cần điều chỉnh tồn kho.');
                return;
            }

            $owns = ProductVariant::query()->where('product_id', $productId)->where('id', (int) $vid)->exists();
            if (! $owns) {
                $v->errors()->add('product_variant_id', 'Phiên bản không thuộc sản phẩm này.');
            }
        });
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
