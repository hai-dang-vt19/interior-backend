@extends('site.base')

@section('content')
<div class="site-auth-wrap">
<div class="row justify-content-center w-100">
    <div class="col-md-5">
        <div class="card site-auth-card">
            <div class="card-body">
                <h4 class="mb-1 site-section-title">Đăng nhập khách hàng</h4>
                <p class="site-muted mb-3">Theo dõi đơn hàng và mua sắm nhanh hơn.</p>
                <form action="{{ route('site.login.submit') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại @include('component.required-mark')</label>
                        <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu @include('component.required-mark')</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                </form>
                <div class="text-center mt-3">
                    <a href="{{ route('site.register') }}">Chưa có tài khoản? Đăng ký</a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
