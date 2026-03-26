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
              <label class="form-label">Tên sản phẩm</label>
              <input type="text" class="form-control" name="name">
            </div>
            <div class="col-md-6">
              <label class="form-label">Danh mục</label>
              <select class="form-select" name="category_id">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Giá</label>
              <input type="number" class="form-control" name="price" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Giá giảm</label>
              <input type="number" class="form-control" name="discount_price" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Số lượng</label>
              <input type="number" class="form-control" name="quantity" min="0">
            </div>
            <div class="col-md-6">
              <label class="form-label">Trạng thái</label>
              <select class="form-select" name="status">
                @foreach (App\Enums\ProductStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Ảnh đại diện (URL)</label>
              <input type="text" class="form-control" name="image_url">
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả</label>
              <textarea class="form-control" rows="3" name="description"></textarea>
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
