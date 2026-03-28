<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Chung Si Interior') }}</title>
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css"
    />
    @vite([
        'resources/scss/app.scss',
        'resources/js/app.js',
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
            <a class="navbar-brand site-brand site-nav-logo" href="{{ route('site.home') }}" title="Trang chủ">icon</a>
            <button class="navbar-toggler site-navbar-toggler border-0 shadow-none px-2" type="button" data-bs-toggle="collapse" data-bs-target="#siteNavbar" aria-controls="siteNavbar" aria-expanded="false" aria-label="Mở menu">
                <span class="site-nav-menu-icon">icon</span>
            </button>
            <div class="collapse navbar-collapse" id="siteNavbar">
                <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 py-3 py-lg-0 ms-lg-auto flex-lg-wrap">
                    @if ($siteCustomer)
                        <span class="site-nav-auth text-nowrap small me-lg-2 pb-2 pb-lg-0 site-nav-auth-sep">
                            <a href="{{ route('site.orders.index') }}" class="link-body-emphasis text-decoration-none">Đơn hàng</a>
                            <span class="site-muted"> / </span>
                            <a href="{{ route('site.cart.index') }}" class="link-body-emphasis text-decoration-none">Giỏ hàng ({{ $siteCartCount }})</a>
                        </span>
                    @else
                        <span class="site-nav-auth text-nowrap small me-lg-2 pb-2 pb-lg-0 site-nav-auth-sep">
                            <a href="{{ route('site.register') }}" class="link-body-emphasis text-decoration-none">Đăng ký</a>
                            <span class="site-muted"> / </span>
                            <a href="{{ route('site.login') }}" class="link-body-emphasis text-decoration-none">Đăng nhập</a>
                        </span>
                    @endif
                    <a href="{{ route('site.home') }}" class="btn btn-sm btn-outline-dark">Trang chủ</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="siteThemeToggle">Dark mode</button>
                    @if ($siteCustomer)
                        <form action="{{ route('site.logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100 w-lg-auto">Đăng xuất</button>
                        </form>
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

    <script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
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
