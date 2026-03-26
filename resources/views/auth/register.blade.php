@extends('auth.base')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-5">
            <div class="login-form">
                <h3 class="text-center mb-4">Đăng ký tài khoản Staff</h3>
                <form method="POST" action="{{ route('admin.register.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Họ tên</label>
                        <input type="text" class="form-control" name="full_name" value="{{ old('full_name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control input-number" name="phone" value="{{ old('phone') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger py-2">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    @if (session('dataError'))
                        <div class="alert alert-danger py-2">
                            {{ session('dataError') }}
                        </div>
                    @endif
                    @if (session('dataSuccess'))
                        <div class="alert alert-success py-2">
                            {{ session('dataSuccess') }}
                        </div>
                    @endif
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Đăng ký</button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.login') }}">Quay lại đăng nhập</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
