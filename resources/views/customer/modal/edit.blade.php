<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Cập nhật</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" class="row gap-3">
            @csrf
            <div class="col-12">
              <label for="full_name" class="form-label">Họ tên</label>
              <input type="text" class="form-control" id="full_name" name="full_name">
            </div>
            <div class="col-12">
              <label for="email" class="form-label">Email</label>
              <input type="text" class="form-control input-number" id="email" name="email" readonly>
            </div>
            <div class="col-12">
              <label for="phone" class="form-label">Phone</label>
              <input type="text" class="form-control input-number" id="phone" name="phone" readonly>
            </div>
            <div class="col-12">
                <label for="loyalty_tier" class="form-label">Hạng khách hàng</label>
                <select class="form-select" id="loyalty_tier" name="loyalty_tier">
                    <option value="standard">Standard</option>
                    <option value="silver">Silver</option>
                    <option value="gold">Gold</option>
                    <option value="platinum">Platinum</option>
                </select>
            </div>
            <div class="col-12">
                <label for="reward_points" class="form-label">Điểm thưởng</label>
                <input type="number" min="0" class="form-control" id="reward_points" name="reward_points">
            </div>
            <div class="col-12 select-select">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-select" id="status" name="status">
                    <option value="{{ App\Enums\CustomerStatus::ACTIVE->value }}">
                        {{ App\Enums\CustomerStatus::ACTIVE->label() }}
                    </option>
                    <option value="{{ App\Enums\CustomerStatus::INACTIVE->value }}">
                        {{ App\Enums\CustomerStatus::INACTIVE->label() }}
                    </option>
                </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="button" class="btn btn-primary btn-submit-edit">Lưu</button>
        </div>
      </div>
    </div>
</div>
