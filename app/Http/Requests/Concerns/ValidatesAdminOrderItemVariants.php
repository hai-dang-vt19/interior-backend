<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Models\ProductVariant;
use Illuminate\Validation\Validator;

trait ValidatesAdminOrderItemVariants
{
    /**
     * Đảm bảo product_variant_id (nếu gửi) thuộc sản phẩm và còn hoạt động.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $items = $this->input('order_items', []);
            if (! is_array($items)) {
                return;
            }
            foreach ($items as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $pid = (int) ($row['product_id'] ?? 0);
                if ($pid <= 0) {
                    continue;
                }
                $rawVid = $row['product_variant_id'] ?? null;
                if ($rawVid === null || $rawVid === '') {
                    continue;
                }
                $vid = (int) $rawVid;
                $ok = ProductVariant::query()
                    ->where('id', $vid)
                    ->where('product_id', $pid)
                    ->where('is_active', true)
                    ->exists();
                if (! $ok) {
                    $v->errors()->add(
                        "order_items.{$i}.product_variant_id",
                        'Phiên bản không thuộc sản phẩm hoặc không còn hoạt động.'
                    );
                }
            }
        });
    }
}
