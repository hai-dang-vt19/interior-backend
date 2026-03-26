<div class="modal fade" id="modalCreateProduct" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Thêm mới sản phẩm</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.product.store') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-6">
              <label class="form-label">Tên sản phẩm</label>
              <input type="text" class="form-control" name="name" value="{{ old('name') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Danh mục</label>
              <select class="form-select" name="category_id">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Giá</label>
              <input type="number" class="form-control" name="price" min="0" value="{{ old('price') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Giá giảm</label>
              <input type="number" class="form-control" name="discount_price" min="0" value="{{ old('discount_price') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Số lượng</label>
              <input type="number" class="form-control" name="quantity" min="0" value="{{ old('quantity', 0) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Trạng thái</label>
              <select class="form-select" name="status">
                @foreach (App\Enums\ProductStatus::cases() as $status)
                    <option value="{{ $status->value }}" {{ old('status', App\Enums\ProductStatus::ACTIVE->value) == $status->value ? 'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Ảnh đại diện (URL)</label>
              <input type="text" class="form-control" name="image_url" value="{{ old('image_url') }}">
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả</label>
              <textarea class="form-control" rows="3" name="description">{{ old('description') }}</textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="button" class="btn btn-success btn-submit-create-product">Tạo mới</button>
        </div>
      </div>
    </div>
</div>
