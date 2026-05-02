<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->order_code ?? '#'.$order->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .wrap { width: 100%; }
        h1, h3 { margin: 0 0 8px 0; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .right { text-align: right; }
        .mt { margin-top: 16px; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Hoa don don hang</h1>
        <p class="muted">Ma don: {{ $order->order_code ?? '#'.$order->id }} | Ngay tao: {{ $order->created_at?->format('d/m/Y H:i') }}</p>

        <h3 class="mt">Thong tin khach hang</h3>
        <p>Ten: {{ $order->customer?->full_name }}</p>
        <p>Dien thoai: {{ $order->shipping_phone }}</p>
        <p>Dia chi giao hang: {{ $order->shipping_address }}</p>

        <h3 class="mt">Chi tiet don hang</h3>
        <table>
            <thead>
                <tr>
                    <th>San pham</th>
                    <th>So luong</th>
                    <th>Don gia</th>
                    <th>Thanh tien</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    @php($variantTxt = \App\Support\ProductLinePricing::variantSummary($item->productVariant))
                    <tr>
                        <td>
                            {{ $item->product?->name }}
                            @if ($variantTxt)
                                <span class="muted"> — {{ $variantTxt }}</span>
                            @endif
                            @php($pv = $item->productVariant)
                            @php($prod = $item->product)
                            @if ($prod && $pv)
                                @php($b = \App\Support\ProductLinePricing::baseUnit($prod))
                                @php($a = \App\Support\ProductLinePricing::variantAddon($pv))
                                @if ($a > 0)
                                    <div class="muted">(Giá SP: {{ number_format($b, 0, ',', '.') }} đ + Phiên bản: {{ number_format($a, 0, ',', '.') }} đ)</div>
                                @endif
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td class="right">{{ number_format((float)$item->price, 0, ',', '.') }} đ</td>
                        <td class="right">{{ number_format((float)$item->price * $item->quantity, 0, ',', '.') }} đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3 class="mt right">Tong cong: {{ $order->getTotalDisplay() }}</h3>
    </div>
</body>
</html>
