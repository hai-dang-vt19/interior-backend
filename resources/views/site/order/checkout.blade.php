@extends('site.base')

@section('content')
<h4 class="mb-3 site-section-title">Thanh toán đơn hàng</h4>

@php($total = 0)
<div class="row g-4">
    <div class="col-md-7">
        <div class="card site-panel">
            <div class="card-body">
                <form action="{{ route('site.checkout.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="selected_items" value="{{ old('selected_items', $selectedItemsCsv ?? '') }}">
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ nhận hàng</label>
                        <textarea class="form-control" name="shipping_address" rows="3" required>{{ old('shipping_address', $defaultShippingAddress ?? '') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại nhận hàng</label>
                        <input type="text" class="form-control" name="shipping_phone" value="{{ old('shipping_phone', auth()->guard('customer')->user()->phone) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phương thức thanh toán</label>
                        <select class="form-select" name="payment_method" required>
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method->value }}">{{ $method->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Đặt hàng</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card site-panel">
            <div class="card-header">Tóm tắt giỏ hàng</div>
            <ul class="list-group list-group-flush">
                @foreach (($checkoutItems ?? $cart->items) as $item)
                    @php($line = ((float) $item->price) * ((int) $item->quantity))
                    @php($total += $line)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $item->product?->name }} x {{ (int) $item->quantity }}</span>
                        <strong>{{ number_format($line, 0, ',', '.') }} đ</strong>
                    </li>
                @endforeach
            </ul>
            <div class="card-footer d-flex justify-content-between">
                <strong>Tổng cộng</strong>
                <strong class="text-danger">{{ number_format($total, 0, ',', '.') }} đ</strong>
            </div>
        </div>
    </div>
</div>
@endsection
