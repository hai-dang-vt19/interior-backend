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
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Tồn kho sản phẩm: {{ $product->name }}</h5>
    </div>
    <div class="card-body">
        <p class="mb-3">Số lượng hiện tại: <strong>{{ $product->inventory?->quantity ?? $product->quantity }}</strong></p>
        <form action="{{ route('admin.product.inventory.adjust', $product->id) }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-3">
                <label class="form-label">Loại điều chỉnh</label>
                <select name="type" class="form-select">
                    @foreach (App\Enums\InventoryType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Số lượng</label>
                <input type="number" name="quantity" class="form-control" min="1" required>
            </div>
            <div class="col-md-6">
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
                        <th>Số lượng</th>
                        <th>Ghi chú</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($product->inventoryHistories as $history)
                        <tr>
                            <td>{{ $history->id }}</td>
                            <td>{{ $history->type?->label() }}</td>
                            <td>{{ $history->quantity }}</td>
                            <td>{{ $history->notes }}</td>
                            <td>{{ $history->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Chưa có lịch sử</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
