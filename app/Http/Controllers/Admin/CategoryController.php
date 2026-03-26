<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CategoryEditRequest;
use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function index(Request $request)
    {
        $categories = $this->categoryService->getCategories($request->all());
        $parentCategories = $this->categoryService->getParentCategories();

        return view('category.index', compact('categories', 'parentCategories'));
    }

    public function store(CategoryRequest $request)
    {
        $this->categoryService->createCategory($request->validated());

        return redirect()->back()->with('dataSuccess', 'Tạo mới danh mục thành công');
    }

    public function edit(int $id)
    {
        return $this->categoryService->getCategoryByID($id);
    }

    public function update(int $id, CategoryEditRequest $request)
    {
        $this->categoryService->updateCategoryByID($id, $request->validated());

        return redirect()->back()->with('dataSuccess', 'Cập nhật danh mục thành công');
    }

    public function destroy(int $id)
    {
        $this->categoryService->destroy($id);

        return redirect()->back()->with('dataSuccess', 'Xóa danh mục thành công');
    }

    public function restore(int $id)
    {
        $this->categoryService->restore($id);

        return redirect()->back()->with('dataSuccess', 'Khôi phục danh mục thành công');
    }

    public function forceDelete(int $id)
    {
        $this->categoryService->forceDelete($id);

        return redirect()->back()->with('dataSuccess', 'Xóa vĩnh viễn danh mục thành công');
    }
}
