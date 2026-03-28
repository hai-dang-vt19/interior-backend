@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.product.index') }}">Sản phẩm</a></li>
            <li class="breadcrumb-item active" aria-current="page">Quản lý ảnh</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Ảnh sản phẩm: {{ $product->name }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.product.images.store', $product->id) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-10">
                <label class="form-label">Chọn ảnh (giữ Ctrl/Cmd để chọn nhiều; mỗi file tối đa 5MB, tối đa 30 ảnh/lần)</label>
                <input type="file" name="images[]" class="form-control" accept="image/*" multiple required>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-success">Tải lên</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th>Preview</th>
                        <th>Đường dẫn lưu</th>
                        <th class="text-center">Ảnh chính</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($product->images as $image)
                        <tr>
                            <td class="text-center">{{ $image->id }}</td>
                            <td>
                                <img src="{{ \App\Models\ProductImage::resolvePublicUrl($image->image_url) }}" alt="product image" style="height:50px; width:50px; object-fit:cover;">
                            </td>
                            <td><small class="text-break">{{ $image->image_url }}</small></td>
                            <td class="text-center">
                                @if ($image->is_primary)
                                    <span class="badge text-bg-primary">Đang là ảnh chính</span>
                                @else
                                    <form action="{{ route('admin.product.images.primary', [$product->id, $image->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-primary btn-sm">Chọn làm ảnh chính</button>
                                    </form>
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('admin.product.images.destroy', [$product->id, $image->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm btn-delete-image">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Chưa có ảnh</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
    $(document).ready(function() {
        $('.btn-delete-image').on('click', function() {
            Alert.confirm({
                title: 'Xóa ảnh sản phẩm',
                text: 'Bạn có chắc chắn muốn xóa ảnh này?',
                confirmButtonText: 'Xóa',
                denyButtonText: 'Hủy',
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).closest('form').submit();
                }
            });
        });
    });
</script>
@endsection
