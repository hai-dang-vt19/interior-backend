<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Thêm mới khách hàng</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.customer.store') }}" method="POST" class="row gap-3">
            @csrf
            <div class="col-12">
              <label for="create_full_name" class="form-label">Họ tên @include('component.required-mark')</label>
              <input type="text" class="form-control" id="create_full_name" name="full_name" value="{{ old('full_name') }}">
            </div>
            <div class="col-12">
              <label for="create_email" class="form-label">Email @include('component.required-mark')</label>
              <input type="email" class="form-control" id="create_email" name="email" value="{{ old('email') }}">
            </div>
            <div class="col-12">
              <label for="create_phone" class="form-label">Số điện thoại @include('component.required-mark')</label>
              <input type="text" class="form-control input-number" id="create_phone" name="phone" value="{{ old('phone') }}">
            </div>
            <div class="col-12">
                <label for="create_loyalty_tier" class="form-label">Hạng khách hàng</label>
                <select class="form-select" id="create_loyalty_tier" name="loyalty_tier">
                    <option value="standard" {{ old('loyalty_tier', 'standard') === 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="silver" {{ old('loyalty_tier') === 'silver' ? 'selected' : '' }}>Silver</option>
                    <option value="gold" {{ old('loyalty_tier') === 'gold' ? 'selected' : '' }}>Gold</option>
                    <option value="platinum" {{ old('loyalty_tier') === 'platinum' ? 'selected' : '' }}>Platinum</option>
                </select>
            </div>
            <div class="col-12">
                <label for="create_reward_points" class="form-label">Điểm thưởng</label>
                <input type="number" min="0" class="form-control" id="create_reward_points" name="reward_points" value="{{ old('reward_points', 0) }}">
            </div>
            <div class="col-12">
                <label for="create_status" class="form-label">Trạng thái @include('component.required-mark')</label>
                <select class="form-select" id="create_status" name="status">
                    <option value="{{ App\Enums\CustomerStatus::ACTIVE->value }}" {{ old('status') == App\Enums\CustomerStatus::ACTIVE->value ? 'selected' : '' }}>
                        {{ App\Enums\CustomerStatus::ACTIVE->label() }}
                    </option>
                    <option value="{{ App\Enums\CustomerStatus::INACTIVE->value }}" {{ old('status') == App\Enums\CustomerStatus::INACTIVE->value ? 'selected' : '' }}>
                        {{ App\Enums\CustomerStatus::INACTIVE->label() }}
                    </option>
                </select>
            </div>
            <div class="col-12">
                <small class="text-muted">Mật khẩu mặc định của khách hàng mới: 12345678</small>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="button" class="btn btn-success btn-submit-create">Tạo mới</button>
        </div>
      </div>
    </div>
</div>
