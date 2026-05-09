@extends('site.base')

@section('content')
    <div class="site-auth-wrap">
        <div class="row justify-content-center w-100">
            <div class="col-md-7">
                <div class="card site-auth-card">
                    <div class="card-body text-center py-5">
                        @if ($status === 'success' || $status === 'already_verified')
                            <div class="mb-3">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success p-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor"
                                        viewBox="0 0 16 16" aria-hidden="true">
                                        <path
                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                    </svg>
                                </span>
                            </div>
                            @if ($status === 'already_verified')
                                <h1 class="h4 site-section-title mb-2">Email đã được xác thực</h1>
                                <p class="site-muted mb-0">Tài khoản của bạn đã được kích hoạt trước đó. Bạn có thể đăng nhập
                                    ngay.</p>
                            @else
                                <h1 class="h4 site-section-title mb-2">Xác thực email thành công</h1>
                                <p class="site-muted mb-0">Tài khoản đã được kích hoạt. Bạn có thể đóng cửa sổ này và đăng
                                    nhập trên website.</p>
                            @endif
                            <div class="mt-4">
                                <a href="{{ route('site.home') }}" class="btn btn-success">Về trang chủ</a>
                                <button type="button" class="btn btn-outline-secondary ms-2 js-open-auth-modal"
                                    data-auth-tab="login">Đăng nhập</button>
                            </div>
                        @elseif ($status === 'expired')
                            <h1 class="h4 site-section-title mb-2">Liên kết đã hết hạn</h1>
                            <p class="site-muted mb-0">Liên kết xác thực không còn hiệu lực. Vui lòng liên hệ hỗ trợ hoặc
                                đăng ký lại nếu cần.</p>
                            <div class="mt-4">
                                <a href="{{ route('site.home') }}" class="btn btn-primary">Về trang chủ</a>
                            </div>
                        @else
                            <h1 class="h4 site-section-title mb-2">Không thể xác thực</h1>
                            <p class="site-muted mb-0">Liên kết không hợp lệ hoặc không tồn tại. Hãy kiểm tra lại email và
                                thử mở đúng liên kết được gửi kèm.</p>
                            <div class="mt-4">
                                <a href="{{ route('site.home') }}" class="btn btn-primary">Về trang chủ</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
