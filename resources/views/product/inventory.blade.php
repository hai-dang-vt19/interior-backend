@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.product.index') }}">Sản phẩm</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tồn kho</li>
        </ol>
    </nav>
@endsection

@section('content')
    @php
        $variants = $product->variants ?? collect();
        $hasVariants = $variants->isNotEmpty();
        $inventoryQty = $hasVariants ? $variants->sum(fn ($v) => (int) $v->quantity) : (int) ($product->inventory?->quantity ?? $product->quantity);
    @endphp

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Tồn kho sản phẩm: {{ $product->name }}</h5>
    </div>
    <div class="card-body">
        <p class="mb-3">Tổng tồn đang có: <strong>{{ $inventoryQty }}</strong></p>

        @if ($hasVariants)
            <div class="table-responsive mb-4">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>SKU phiên bản</th>
                            <th>Màu / chất liệu</th>
                            <th class="text-end">Tồn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($variants as $v)
                            <tr>
                                <td>{{ $v->sku_variant ?: '—' }} @if($v->is_default)<span class="badge bg-secondary">Mặc định</span>@endif</td>
                                <td>{{ $v->color_name ?: '—' }} @if ($v->material_main) · {{ $v->material_main }} @endif</td>
                                <td class="text-end"><strong>{{ (int) $v->quantity }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <h6 class="mb-3">Điều chỉnh tồn theo phiên bản</h6>
        @else
            <p class="text-muted small mb-3">Sản phẩm chỉ có một mã SP — nhập hoặc xuất như một dòng duy nhất.</p>
        @endif

        <form action="{{ route('admin.product.inventory.adjust', $product->id) }}" method="POST" class="row g-3">
            @csrf
            @if ($hasVariants)
                <div class="col-md-4">
                    <label class="form-label">Phiên bản</label>
                    <select name="product_variant_id" class="form-select @error('product_variant_id') is-invalid @enderror" required>
                        <option value="">— Chọn phiên bản —</option>
                        @foreach ($variants as $v)
                            <option value="{{ $v->id }}">{{ $v->sku_variant ?: '#' . $v->id }} · {{ $v->color_name ?: '' }} · Tồn: {{ (int) $v->quantity }}</option>
                        @endforeach
                    </select>
                    @error('product_variant_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endif
            <div class="{{ $hasVariants ? 'col-md-2' : 'col-md-3' }}">
                <label class="form-label">Loại điều chỉnh</label>
                <select name="type" class="form-select">
                    @foreach (App\Enums\InventoryType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="{{ $hasVariants ? 'col-md-2' : 'col-md-3' }}">
                <label class="form-label">Số lượng</label>
                <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" min="1" required value="{{ old('quantity') }}">
                @error('quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="{{ $hasVariants ? 'col-md-4' : 'col-md-6' }}">
                <label class="form-label">Ghi chú</label>
                <input type="text" name="notes" class="form-control">
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit">Cập nhật tồn kho</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Lịch sử điều chỉnh gần đây</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Loại</th>
                        <th>Phiên bản</th>
                        <th>Số lượng</th>
                        <th>Ghi chú</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($product->inventoryHistories as $history)
                        @php($vInfo = \App\Support\ProductLinePricing::variantSummary($history->productVariant))
                        <tr>
                            <td>{{ $history->id }}</td>
                            <td>{{ $history->type?->label() }}</td>
                            <td>{{ $vInfo ?? '—' }}</td>
                            <td>{{ $history->quantity }}</td>
                            <td>{{ $history->notes }}</td>
                            <td>{{ $history->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Chưa có lịch sử</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
