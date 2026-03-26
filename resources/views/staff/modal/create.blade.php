<div class="modal fade" id="modalCreateStaff" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Thêm mới nhân viên</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.staff.store') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-6">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" name="username">
            </div>
            <div class="col-md-6">
              <label class="form-label">Họ tên</label>
              <input type="text" class="form-control" name="full_name">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email">
            </div>
            <div class="col-md-6">
              <label class="form-label">Số điện thoại</label>
              <input type="text" class="form-control" name="phone">
            </div>
            <div class="col-12">
              <label class="form-label">Mật khẩu</label>
              <input type="password" class="form-control" name="password">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="button" class="btn btn-success btn-submit-create-staff">Tạo mới</button>
        </div>
      </div>
    </div>
</div>
