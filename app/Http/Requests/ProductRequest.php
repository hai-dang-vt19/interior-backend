<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class ProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $variants = collect($this->input('variants', []))
            ->map(function ($row) {
                if (! is_array($row)) {
                    return $row;
                }

                $row['is_default'] = filter_var($row['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $row['is_active'] = filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);

                return $row;
            })
            ->all();

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_customizable' => $this->boolean('is_customizable'),
            'variants' => $variants,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:200'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'description' => ['nullable', 'string'],
            'description_short' => ['nullable', 'string', 'max:500'],
            'description_long' => ['nullable', 'string'],
            'style' => ['nullable', 'string', 'max:100'],
            'space_type' => ['nullable', 'string', 'max:150'],
            'origin' => ['nullable', 'string', 'max:100'],
            'year_released' => ['nullable', 'integer', 'between:1900,2100'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'quantity' => ['required', 'integer', 'min:0'],
            'status' => ['required', new Enum(ProductStatus::class)],
            'is_active' => ['nullable', 'boolean'],
            'is_customizable' => ['nullable', 'boolean'],
            'variants' => ['nullable', 'array'],
            'variants.*.sku_variant' => ['nullable', 'string', 'max:120'],
            'variants.*.color_name' => ['nullable', 'string', 'max:100'],
            'variants.*.color_hex' => ['nullable', 'string', 'size:7'],
            'variants.*.material_main' => ['nullable', 'string', 'max:150'],
            'variants.*.material_sub' => ['nullable', 'string', 'max:150'],
            'variants.*.finish' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.currency' => ['nullable', 'string', 'size:3'],
            'variants.*.unit' => ['nullable', 'string', 'max:50'],
            'variants.*.qty_per_set' => ['nullable', 'integer', 'min:1'],
            'variants.*.is_default' => ['nullable', 'boolean'],
            'variants.*.is_active' => ['nullable', 'boolean'],
            'specs' => ['nullable', 'array'],
            'specs.*.length_mm' => ['nullable', 'numeric', 'min:0'],
            'specs.*.width_mm' => ['nullable', 'numeric', 'min:0'],
            'specs.*.height_mm' => ['nullable', 'numeric', 'min:0'],
            'specs.*.weight_kg' => ['nullable', 'numeric', 'min:0'],
            'specs.*.max_load_kg' => ['nullable', 'numeric', 'min:0'],
            'specs.*.spec_key' => ['nullable', 'string', 'max:100'],
            'specs.*.spec_value' => ['nullable', 'string', 'max:255'],
            'specs.*.spec_unit' => ['nullable', 'string', 'max:50'],
            'specs.*.spec_group' => ['nullable', 'string', 'max:100'],
            'specs.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('Validate product create failed', [
            'errors' => $validator->errors()->all(),
            'input' => $this->all(),
            'url' => request()->fullUrl(),
        ]);

        $this->flash();
        session()->flash('dataError', 'Không thành công');

        throw new ValidationException($validator);
    }
}
