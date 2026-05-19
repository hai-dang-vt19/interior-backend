<div class="modal fade" id="modalEditProduct" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Cập nhật sản phẩm</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" class="row g-3">
            @csrf
            <div class="col-md-6">
              <label class="form-label">Tên sản phẩm @include('component.required-mark')</label>
              <input type="text" class="form-control" name="name">
            </div>
            <div class="col-md-6">
              <label class="form-label">SKU</label>
              <input type="text" class="form-control js-product-sku-display" maxlength="100" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label">Danh mục @include('component.required-mark')</label>
              <select class="form-select" name="category_id">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Giá @include('component.required-mark')</label>
              <input type="number" class="form-control" name="price" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Giá giảm</label>
              <input type="number" class="form-control" name="discount_price" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Số lượng (khi không có phiên bản)</label>
              <input type="number" class="form-control" name="quantity" min="0">
              <p class="form-text mb-0">Có phiên bản — tồn nhập trong từng dòng; tổng SP đồng bộ sau khi lưu.</p>
            </div>
            <div class="col-md-12">
              <label class="form-label">Trạng thái @include('component.required-mark')</label>
              <select class="form-select" name="status">
                @foreach (App\Enums\ProductStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Phong cách</label>
              <input type="text" class="form-control" name="style" maxlength="100">
            </div>
            <div class="col-md-4">
              <label class="form-label">Không gian</label>
              <input type="text" class="form-control" name="space_type" maxlength="150">
            </div>
            <div class="col-md-4">
              <label class="form-label">Xuất xứ</label>
              <input type="text" class="form-control" name="origin" maxlength="100">
            </div>
            <div class="col-md-4">
              <label class="form-label">Năm ra mắt</label>
              <input type="number" class="form-control" name="year_released" min="1900" max="2100">
            </div>
            <div class="col-md-4 form-check mt-4 ms-2">
              <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
              <label class="form-check-label" for="edit_is_active">Hiển thị sản phẩm</label>
            </div>
            <div class="col-md-4 form-check mt-4 ms-2">
              <input class="form-check-input" type="checkbox" name="is_customizable" id="edit_is_customizable" value="1">
              <label class="form-check-label" for="edit_is_customizable">Cho phép tùy chỉnh</label>
            </div>
            <div class="col-12">
              <p class="form-text text-muted mb-0">Ảnh sản phẩm quản lý tại <strong>Quản lý ảnh</strong>.</p>
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả ngắn</label>
              <textarea class="form-control" rows="2" name="description_short"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả</label>
              <textarea class="form-control" rows="3" name="description"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả dài</label>
              <textarea class="form-control" rows="4" name="description_long"></textarea>
            </div>
            <div class="col-12">
              <hr>
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Phiên bản sản phẩm</h6>
                <button type="button" class="btn btn-sm btn-outline-primary js-add-variant-row" data-target="#modalEditProduct">
                  + Thêm phiên bản
                </button>
              </div>
              <p class="form-text mb-1">Dữ liệu trong modal sẽ ghi đè danh sách variants/specs hiện tại.</p>
            </div>
            <div class="col-12">
              <div class="row g-2 js-variants-container" data-prefix="variants"></div>
            </div>
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Thông số kỹ thuật</h6>
                <button type="button" class="btn btn-sm btn-outline-primary js-add-spec-row" data-target="#modalEditProduct">
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
          <button type="button" class="btn btn-primary btn-submit-edit-product">Lưu</button>
        </div>
      </div>
    </div>
</div>
