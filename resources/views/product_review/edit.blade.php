@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.product-review.index') }}">Đánh giá sản phẩm</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sửa #{{ $review->id }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Sửa đánh giá #{{ $review->id }}</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Sản phẩm: @if ($review->product) #{{ $review->product->id }} — {{ $review->product->name }} @else — @endif
            <br>
            Khách: {{ $review->customer?->full_name ?? '—' }} @if ($review->customer?->email) ({{ $review->customer->email }}) @endif
        </p>
        <form action="{{ route('admin.product-review.update', $review->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="mb-3">
                <label class="form-label">Số sao (1–5) @include('component.required-mark')</label>
                <select name="rating" class="form-select @error('rating') is-invalid @enderror" required>
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" @selected((int) old('rating', $review->rating) === $i)>{{ $i }} sao</option>
                    @endfor
                </select>
                @error('rating')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Nội dung đánh giá @include('component.required-mark')</label>
                <textarea name="review" class="form-control @error('review') is-invalid @enderror" rows="6" required>{{ old('review', $review->review) }}</textarea>
                @error('review')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Lưu</button>
            <a href="{{ route('admin.product-review.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </form>
    </div>
</div>
@endsection
