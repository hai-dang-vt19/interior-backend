@php
    $footerMenuCategories = \App\Models\Category::query()
        ->whereNull('parent_id')
        ->orderBy('name')
        ->get(['id', 'name']);
@endphp

<footer class="site-footer" role="contentinfo">
    <div class="site-footer__inner">
        <div class="container py-5">
            <div class="row g-4 g-lg-5">
                <div class="col-lg-4">
                    <a href="{{ route('site.home') }}" class="site-footer__brand">Chung Si Interior</a>
                    <p class="site-footer__lead">
                        Nội thất tinh gọn, phong cách hiện đại — lựa chọn cho không gian sống của bạn.
                    </p>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <h2 class="site-footer__section-title">Danh mục</h2>
                    @if ($footerMenuCategories->isEmpty())
                        <p class="site-footer__empty text-muted mb-0">Chưa có danh mục.</p>
                    @else
                        <nav class="site-footer__nav" aria-label="Danh mục sản phẩm (cấp cha)">
                            <ul class="site-footer__list">
                                @foreach ($footerMenuCategories as $category)
                                    <li>
                                        <a href="{{ route('site.products.index', ['category_id' => $category->id]) }}"
                                            class="site-footer__link">
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </nav>
                    @endif
                </div>
                <div class="col-sm-6 col-lg-4">
                    <h2 class="site-footer__section-title">Khám phá</h2>
                    <nav class="site-footer__nav" aria-label="Liên kết nhanh">
                        <ul class="site-footer__list">
                            <li>
                                <a href="{{ route('site.home') }}" class="site-footer__link">Trang chủ</a>
                            </li>
                            <li>
                                <a href="{{ route('site.products.index') }}" class="site-footer__link">Sản phẩm</a>
                            </li>
                            @auth('customer')
                                <li>
                                    <a href="{{ route('site.account') }}" class="site-footer__link">Tài khoản</a>
                                </li>
                                <li>
                                    <a href="{{ route('site.orders.index') }}" class="site-footer__link">Đơn hàng</a>
                                </li>
                            @endauth
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="site-footer__bar">
                <p class="site-footer__copy mb-0">
                    © {{ date('Y') }} <span lang="vi">Chung Si Interior</span>. Đã đăng ký bản quyền.
                </p>
                <p class="site-footer__copy mb-0">
                    HOTLINE: 0947 508 288 | Email: info@chungsiinterior.com
                </p>
            </div>
        </div>
    </div>
</footer>
