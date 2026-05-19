@extends('site.base')

@section('content')
<div class="site-auth-wrap">
<div class="row justify-content-center w-100">
    <div class="col-md-6">
        <div class="card site-auth-card">
            <div class="card-body">
                <h4 class="mb-1 site-section-title">Đăng ký khách hàng</h4>
                <p class="site-muted mb-3">Tạo tài khoản để lưu giỏ hàng và xem lịch sử mua.</p>
                <form action="{{ route('site.register.submit') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Họ tên @include('component.required-mark')</label>
                        <input type="text" class="form-control" name="full_name" value="{{ old('full_name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email @include('component.required-mark')</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại @include('component.required-mark')</label>
                        <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu @include('component.required-mark')</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Xác nhận mật khẩu @include('component.required-mark')</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Đăng ký</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
