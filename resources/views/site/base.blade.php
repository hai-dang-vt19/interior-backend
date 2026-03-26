<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Chung Si Interior') }}</title>
    @vite([
        'resources/scss/app.scss',
        'resources/js/app.js',
        'resources/scss/custom.scss',
    ])
    <script>
        (() => {
            const saved = localStorage.getItem('site-theme');
            if (saved === 'dark') {
                document.documentElement.setAttribute('data-site-theme', 'dark');
            }
        })();
    </script>
</head>
<body class="site-body">
    @php($siteCustomer = auth()->guard('customer')->user())
    @php($siteCartCount = $siteCustomer ? \App\Models\CartItem::query()->whereHas('cart', fn($q) => $q->where('customer_id', $siteCustomer->id))->count() : 0)
    <nav class="navbar navbar-expand-lg navbar-light site-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand site-brand" href="{{ route('site.home') }}">Chung Si Interior</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNavbar" aria-controls="siteNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="siteNavbar">
                <div class="ms-auto d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-2 py-2 py-lg-0">
                    <a href="{{ route('site.home') }}" class="btn btn-sm btn-outline-dark">Trang chủ</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="siteThemeToggle">Dark mode</button>
                    @if ($siteCustomer)
                        <a href="{{ route('site.orders.index') }}" class="btn btn-sm btn-outline-primary">Đơn hàng của tôi</a>
                        <a href="{{ route('site.cart.index') }}" class="btn btn-sm btn-outline-success">Giỏ hàng ({{ $siteCartCount }})</a>
                        <form action="{{ route('site.logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">Đăng xuất</button>
                        </form>
                    @else
                        <a href="{{ route('site.login') }}" class="btn btn-sm btn-outline-primary">Đăng nhập</a>
                        <a href="{{ route('site.register') }}" class="btn btn-sm btn-outline-success">Đăng ký</a>
                    @endif
                    <a href="{{ route('admin.login') }}" class="btn btn-sm btn-dark">Quản trị</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>
    <div class="toast-container position-fixed top-0 end-0 p-3 site-toast-wrap">
        @if (session('dataSuccess'))
            <div class="toast align-items-center text-bg-success border-0 show mb-2 site-toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
                <div class="d-flex">
                    <div class="toast-body">{{ session('dataSuccess') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
        @if (session('dataError') || $errors->any())
            <div class="toast align-items-center text-bg-danger border-0 show mb-2 site-toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body">{{ session('dataError') ?: $errors->first() }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>
    @yield('scripts')
    <script type="module">
        document.querySelectorAll('.site-toast').forEach((el) => {
            setTimeout(() => el.classList.remove('show'), Number(el.dataset.bsDelay || 3000));
        });

        const toggleBtn = document.getElementById('siteThemeToggle');
        const root = document.documentElement;
        const applyBtnLabel = () => {
            const isDark = root.getAttribute('data-site-theme') === 'dark';
            if (toggleBtn) {
                toggleBtn.textContent = isDark ? 'Light mode' : 'Dark mode';
            }
        };

        if (toggleBtn) {
            applyBtnLabel();
            toggleBtn.addEventListener('click', () => {
                const isDark = root.getAttribute('data-site-theme') === 'dark';
                if (isDark) {
                    root.removeAttribute('data-site-theme');
                    localStorage.setItem('site-theme', 'light');
                } else {
                    root.setAttribute('data-site-theme', 'dark');
                    localStorage.setItem('site-theme', 'dark');
                }
                applyBtnLabel();
            });
        }
    </script>
</body>
</html>
