<div class="modal fade" id="modalEditOrder" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Cập nhật đơn hàng</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" class="row g-3">
            @csrf
            <div class="col-md-6">
              <label class="form-label">Khách hàng</label>
              <select class="form-select" name="customer_id">
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->full_name }} - {{ $customer->phone }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Số điện thoại giao hàng</label>
              <input type="text" class="form-control" name="shipping_phone">
            </div>
            <div class="col-md-6">
              <label class="form-label">Đơn vị vận chuyển</label>
              <input type="text" class="form-control" name="shipping_provider">
            </div>
            <div class="col-md-6">
              <label class="form-label">Mã vận đơn</label>
              <input type="text" class="form-control" name="tracking_number">
            </div>
            <div class="col-md-6">
              <label class="form-label">Trạng thái đơn</label>
              <select class="form-select" name="status">
                @foreach (App\Enums\OrderStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phương thức thanh toán</label>
              <select class="form-select" name="payment_method">
                @foreach (App\Enums\PaymentMethod::forAdminOrderForm() as $method)
                    <option value="{{ $method->value }}">{{ $method->label() }}@if (! in_array($method, App\Enums\PaymentMethod::forSiteCheckout(), true)) (dữ liệu cũ)@endif</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Trạng thái thanh toán</label>
              <select class="form-select" name="payment_status">
                @foreach (App\Enums\PaymentStatus::cases() as $payStatus)
                    <option value="{{ $payStatus->value }}">{{ $payStatus->label() }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Địa chỉ giao hàng</label>
              <textarea class="form-control" name="shipping_address" rows="2"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Ghi chú</label>
              <textarea class="form-control" name="notes" rows="2"></textarea>
            </div>
            <div class="col-12">
              @if (auth()->user()->role === \App\Enums\UserRole::ADMIN)
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <label class="form-label mb-0">Sản phẩm trong đơn</label>
                  <button type="button" class="btn btn-sm btn-outline-primary btn-add-order-item-edit">Thêm dòng</button>
                </div>
                <div class="order-items-edit"></div>
                <small class="text-muted">Đơn giá đồng bộ website; tổng thanh toán trừ chiết khấu theo hạng khách khi lưu. Hạng áp dụng được lưu trên đơn.</small>
              @else
                <label class="form-label mb-2">Sản phẩm trong đơn</label>
                <p class="text-muted small mb-2">
                  Chỉ <strong>Administrator</strong> được thêm, xóa hoặc đổi dòng sản phẩm. Danh sách bên dưới chỉ xem.
                </p>
                <div class="order-items-edit-readonly border rounded p-0 overflow-hidden bg-light"></div>
              @endif
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="button" class="btn btn-primary btn-submit-edit-order">Lưu</button>
        </div>
      </div>
    </div>
</div>
