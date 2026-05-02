@extends('site.base')

@section('content')
    @php($total = 0)
    @php($itemCount = (int) (($checkoutItems ?? $cart->items)->count()))

    <section class="ck-page">
        <header class="ck-head mb-4">
            <p class="ck-breadcrumb mb-1">
                <a href="{{ route('site.cart.index') }}">Giỏ hàng</a>
                <span>/</span>
                <strong>Thanh toán</strong>
            </p>
            <h1 class="ck-title mb-1">Thanh toán đơn hàng</h1>
            <p class="ck-sub mb-0">Kiểm tra thông tin giao hàng và xác nhận đơn trước khi đặt mua.</p>
        </header>

        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="ck-card">
                    <div class="ck-card-head">
                        <h5 class="mb-0">Thông tin giao hàng</h5>
                    </div>
                    <div class="ck-card-body">
                        <form action="{{ route('site.checkout.submit') }}" method="POST" class="ck-form">
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
                                        <option value="{{ $method->value }}" @selected(old('payment_method') === $method->value)>{{ $method->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-success ck-submit-btn">Đặt hàng</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <aside class="ck-summary-card">
                    <div class="ck-summary-head">
                        <h5 class="mb-0">Tóm tắt đơn hàng</h5>
                        <span>{{ $itemCount }} sản phẩm</span>
                    </div>
                    <ul class="ck-summary-list">
                        @foreach (($checkoutItems ?? $cart->items) as $item)
                            @php($line = ((float) $item->price) * ((int) $item->quantity))
                            @php($total += $line)
                            @php($summaryImage = \App\Models\ProductImage::resolvePublicUrl(optional($item->product?->images?->firstWhere('is_primary', true))->image_url ?: optional($item->product?->images?->first())->image_url))
                            <li class="ck-summary-item">
                                <img src="{{ $summaryImage ?: asset('storage/images/image_default.jpg') }}" alt="{{ $item->product?->name }}" class="ck-summary-thumb" loading="lazy">
                                <div class="ck-summary-main">
                                    <p class="ck-summary-name mb-1">{{ $item->product?->name }}</p>
                                    @include('site.component.line-pricing-note', [
                                        'product' => $item->product,
                                        'variant' => $item->productVariant,
                                        'storedUnit' => $item->price,
                                    ])
                                    <small class="text-muted">Số lượng: {{ (int) $item->quantity }}</small>
                                </div>
                                <strong class="ck-summary-price">{{ number_format($line, 0, ',', '.') }} đ</strong>
                            </li>
                        @endforeach
                    </ul>
                    <div class="ck-summary-total">
                        <span>Tổng cộng</span>
                        <strong>{{ number_format($total, 0, ',', '.') }} đ</strong>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
