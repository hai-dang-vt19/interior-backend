@extends('site.base')

@section('content')
<h4 class="site-section-title site-page-head">Giỏ hàng của bạn</h4>

@if ($cart->items->isEmpty())
    @include('site.component.empty-state', [
        'title' => 'Giỏ hàng đang trống',
        'description' => 'Bạn chưa thêm sản phẩm nào vào giỏ hàng.',
        'actionUrl' => route('site.home'),
        'actionText' => 'Tiếp tục mua sắm',
    ])
@else
    @php($total = 0)
    <div class="table-responsive site-table-wrap">
        <table class="table table-bordered align-middle">
            <thead>
                <tr class="table-light">
                    <th>Sản phẩm</th>
                    <th width="120">Đơn giá</th>
                    <th width="170">Số lượng</th>
                    <th width="140">Thành tiền</th>
                    <th width="110">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cart->items as $item)
                    @php($line = ((float) $item->price) * ((int) $item->quantity))
                    @php($total += $line)
                    <tr>
                        <td>
                            <strong class="d-block">{{ $item->product?->name }}</strong>
                            @include('site.component.line-pricing-note', [
                                'product' => $item->product,
                                'variant' => $item->productVariant,
                                'storedUnit' => $item->price,
                            ])
                        </td>
                        <td>{{ number_format((float) $item->price, 0, ',', '.') }} đ</td>
                        <td>
                            <form action="{{ route('site.cart.items.update', $item->id) }}" method="POST" class="d-flex gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="number" class="form-control" name="quantity" min="1" value="{{ (int) $item->quantity }}">
                                <button class="btn btn-sm btn-primary" type="submit">Lưu</button>
                            </form>
                        </td>
                        <td>{{ number_format($line, 0, ',', '.') }} đ</td>
                        <td>
                            <form action="{{ route('site.cart.items.destroy', $item->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end mb-3 mt-3">
        <h5 class="mb-0">Tổng: {{ number_format($total, 0, ',', '.') }} đ</h5>
    </div>
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('site.home') }}" class="btn btn-outline-dark">Mua thêm</a>
        <a href="{{ route('site.checkout') }}" class="btn btn-success">Tiến hành thanh toán</a>
    </div>
@endif
@endsection
