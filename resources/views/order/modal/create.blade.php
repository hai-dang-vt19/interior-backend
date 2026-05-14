<div class="modal fade" id="modalCreateOrder" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Tạo đơn hàng</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.order.store') }}" method="POST" class="row g-3">
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
              <input type="text" class="form-control" name="shipping_phone" value="{{ old('shipping_phone') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Đơn vị vận chuyển</label>
              <input type="text" class="form-control" name="shipping_provider" value="{{ old('shipping_provider') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Mã vận đơn</label>
              <input type="text" class="form-control" name="tracking_number" value="{{ old('tracking_number') }}">
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
                @foreach (App\Enums\PaymentMethod::forSiteCheckout() as $method)
                    <option value="{{ $method->value }}">{{ $method->label() }}</option>
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
              <textarea class="form-control" name="shipping_address" rows="2">{{ old('shipping_address') }}</textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Ghi chú</label>
              <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
            </div>
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Sản phẩm trong đơn</label>
                <button type="button" class="btn btn-sm btn-outline-primary btn-add-order-item-create">Thêm dòng</button>
              </div>
              <div class="order-items-create"></div>
              <small class="text-muted">Đơn giá đồng bộ website (KM + phiên bản). Tổng thanh toán = tạm tính − chiết khấu % theo <strong>hạng khách</strong> tại thời điểm lưu (cùng luật với đặt trên web); hệ thống lưu hạng đã áp dụng trên đơn.</small>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="button" class="btn btn-success btn-submit-create-order">Tạo mới</button>
        </div>
      </div>
    </div>
</div>
