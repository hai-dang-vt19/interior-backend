<?php

namespace App\Http\Controllers\Site;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function home(Request $request)
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $categoryId = (int) $request->input('category_id', 0);

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $products = Product::query()
            ->with(['category:id,name', 'images'])
            ->where('status', ProductStatus::ACTIVE->value)
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->when($categoryId > 0, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderByDesc('id')
            ->paginate(12)
            ->appends($request->query());

        return view('site.home', compact('products', 'categories', 'keyword', 'categoryId'));
    }

    public function showProduct(int $id)
    {
        $product = Product::query()
            ->with(['category:id,name', 'images'])
            ->where('status', ProductStatus::ACTIVE->value)
            ->findOrFail($id);

        $relatedProducts = Product::query()
            ->with('images')
            ->where('status', ProductStatus::ACTIVE->value)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        return view('site.product-show', compact('product', 'relatedProducts'));
    }
}
