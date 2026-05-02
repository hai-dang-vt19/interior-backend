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
              <label class="form-label">SKU</label>
              <input type="text" class="form-control js-product-sku-display" value="Tự động tạo khi lưu" readonly>
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
              <label class="form-label">Số lượng (SP không có phiên bản)</label>
              <input type="number" class="form-control" name="quantity" min="0" value="{{ old('quantity', 0) }}">
              <p class="form-text mb-0">Nếu thêm phiên bản — nhập <strong>tồn kho</strong> trong từng dòng phiên bản.</p>
            </div>
            <div class="col-md-12">
              <label class="form-label">Trạng thái</label>
              <select class="form-select" name="status">
                @foreach (App\Enums\ProductStatus::cases() as $status)
                    <option value="{{ $status->value }}" {{ old('status', App\Enums\ProductStatus::ACTIVE->value) == $status->value ? 'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Phong cách</label>
              <input type="text" class="form-control" name="style" value="{{ old('style') }}" maxlength="100">
            </div>
            <div class="col-md-4">
              <label class="form-label">Không gian</label>
              <input type="text" class="form-control" name="space_type" value="{{ old('space_type') }}" maxlength="150">
            </div>
            <div class="col-md-4">
              <label class="form-label">Xuất xứ</label>
              <input type="text" class="form-control" name="origin" value="{{ old('origin') }}" maxlength="100">
            </div>
            <div class="col-md-4">
              <label class="form-label">Năm ra mắt</label>
              <input type="number" class="form-control" name="year_released" min="1900" max="2100" value="{{ old('year_released') }}">
            </div>
            <div class="col-md-4 form-check mt-4 ms-2">
              <input class="form-check-input" type="checkbox" name="is_active" id="create_is_active" value="1" @checked(old('is_active', 1))>
              <label class="form-check-label" for="create_is_active">Hiển thị sản phẩm</label>
            </div>
            <div class="col-md-4 form-check mt-4 ms-2">
              <input class="form-check-input" type="checkbox" name="is_customizable" id="create_is_customizable" value="1" @checked(old('is_customizable', 0))>
              <label class="form-check-label" for="create_is_customizable">Cho phép tùy chỉnh</label>
            </div>
            <div class="col-12">
              <p class="form-text text-muted mb-0">Ảnh sản phẩm thêm tại màn <strong>Quản lý ảnh</strong> sau khi tạo sản phẩm.</p>
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả ngắn</label>
              <textarea class="form-control" rows="2" name="description_short">{{ old('description_short') }}</textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả</label>
              <textarea class="form-control" rows="3" name="description">{{ old('description') }}</textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả dài</label>
              <textarea class="form-control" rows="4" name="description_long">{{ old('description_long') }}</textarea>
            </div>
            <div class="col-12">
              <hr>
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Phiên bản sản phẩm</h6>
                <button type="button" class="btn btn-sm btn-outline-primary js-add-variant-row" data-target="#modalCreateProduct">
                  + Thêm phiên bản
                </button>
              </div>
            </div>
            <div class="col-12">
              <div class="row g-2 js-variants-container" data-prefix="variants"></div>
            </div>
            <div class="col-12">
              <hr>
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Thông số kỹ thuật</h6>
                <button type="button" class="btn btn-sm btn-outline-primary js-add-spec-row" data-target="#modalCreateProduct">
                  + Thêm thông số
                </button>
              </div>
            </div>
            <div class="col-12">
              <div class="row g-2 js-specs-container" data-prefix="specs"></div>
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
