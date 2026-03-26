<div class="modal fade" id="modalEditCategory" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Cập nhật danh mục</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" class="row g-3">
            @csrf
            <div class="col-12">
              <label class="form-label">Tên danh mục</label>
              <input type="text" class="form-control" name="name">
            </div>
            <div class="col-12">
              <label class="form-label">Danh mục cha</label>
              <select class="form-select" name="parent_id">
                <option value="">Không có</option>
                @foreach ($parentCategories as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả</label>
              <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="button" class="btn btn-primary btn-submit-edit-category">Lưu</button>
        </div>
      </div>
    </div>
</div>
