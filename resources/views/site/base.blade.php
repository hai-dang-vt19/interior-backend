<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Chung Si Interior') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css" />
    @vite(['resources/scss/app.scss', 'resources/scss/custom.scss', 'resources/js/app.js'])
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
    <header class="site-header site-navbar sticky-top">
        <div class="container">
            <div class="site-nav-bar">
                <a class="site-nav-home site-nav-icon-link" href="{{ route('site.home') }}" title="Trang chủ"
                    aria-label="Trang chủ">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 10.5 12 3l9 7.5" />
                        <path d="M5.25 9.75V20.4c0 .33.27.6.6.6h4.65v-6h3v6h4.65c.33 0 .6-.27.6-.6V9.75" />
                    </svg>
                    <span class="text">Trang chủ</span>
                </a>

                <div class="site-nav-actions">
                    <button type="button" class="site-nav-icon-btn icon-light" id="siteThemeToggle"
                        aria-label="Đổi giao diện sáng tối">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M12 17.01c2.76 0 5.01-2.25 5.01-5.01S14.76 6.99 12 6.99 6.99 9.24 6.99 12s2.25 5.01 5.01 5.01M12 9c1.66 0 3.01 1.35 3.01 3.01s-1.35 3.01-3.01 3.01-3.01-1.35-3.01-3.01S10.34 9 12 9m1 10h-2v3h2zm0-17h-2v3h2zM2 11h3v2H2zm17 0h3v2h-3zM4.22 18.36l.71.71.71.71 1.06-1.06 1.06-1.06-.71-.71-.71-.71-1.06 1.06zM19.78 5.64l-.71-.71-.71-.71-1.06 1.06-1.06 1.06.71.71.71.71 1.06-1.06zm-12.02.7L6.7 5.28 5.64 4.22l-.71.71-.71.71L5.28 6.7l1.06 1.06.71-.71zm8.48 11.32 1.06 1.06 1.06 1.06.71-.71.71-.71-1.06-1.06-1.06-1.06-.71.71z" />
                        </svg>
                    </button>

                    @guest('customer')
                        <div class="site-nav-guest-auth">
                            <button type="button" class="site-nav-guest-auth-btn js-open-auth-modal"
                                data-auth-tab="login">Đăng nhập</button>
                            <button type="button" class="site-nav-guest-auth-btn js-open-auth-modal"
                                data-auth-tab="register">Đăng ký</button>
                        </div>
                    @endguest

                    @if ($siteCustomer)
                        <a class="site-nav-cart-slot site-nav-cart-link" data-bs-toggle="offcanvas"
                            href="#siteCartPanel" role="button" aria-controls="siteCartPanel">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M21 4H6.17l-.18-1.15A1 1 0 0 0 5 2H2v2h2.14l1.87 12.15A1 1 0 0 0 7 17h12v-2H7.86l-.31-2H19c.45 0 .84-.3.96-.73l2-7A1 1 0 0 0 21 3.99Zm-2.75 7H7.24l-.77-5h13.2l-1.43 5ZM8 18a2 2 0 1 0 0 4 2 2 0 1 0 0-4m9 0a2 2 0 1 0 0 4 2 2 0 1 0 0-4">
                                </path>
                            </svg>
                        </a>
                    @endif

                    <button type="button" class="site-nav-hamburger site-nav-icon-btn" id="siteNavMenuToggle"
                        aria-controls="siteNavMenuPanel" aria-expanded="false" aria-label="Mở menu điều hướng">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            aria-hidden="true">
                            <line x1="4" y1="6" x2="20" y2="6" />
                            <line x1="4" y1="12" x2="20" y2="12" />
                            <line x1="4" y1="18" x2="20" y2="18" />
                        </svg>
                    </button>
                </div>

                <div id="siteNavMenuPanel" class="site-nav-menu-panel" hidden>
                    @php(
                        $menuCategories = \App\Models\Category::query()->orderBy('name')->get(['id', 'name'])
                    )
                    @php($menuCategoryColumns = $menuCategories->chunk(max(1, (int) ceil($menuCategories->count() / 3))))

                    <div class="site-nav-menu-quick-links">
                        <a href="{{ route('site.home') }}" class="site-nav-menu-link">Trang chủ</a>
                        <a href="{{ route('site.products.index') }}" class="site-nav-menu-link">Sản phẩm</a>
                        @auth('customer')
                            <a href="{{ route('site.account') }}" class="site-nav-menu-link">Tài khoản</a>
                            <a href="{{ route('site.orders.index') }}" class="site-nav-menu-link">Đơn hàng</a>
                            <form action="{{ route('site.logout') }}" method="POST" class="site-nav-pill-form">
                                @csrf
                                <button type="submit" class="site-nav-menu-link site-nav-menu-link-btn">Đăng xuất</button>
                            </form>
                        @endauth
                    </div>

                    <div class="site-nav-category-grid">
                        @foreach ($menuCategoryColumns as $column)
                            <div class="site-nav-category-col">
                                @foreach ($column as $category)
                                    <a href="{{ route('site.products.index', ['category_id' => $category->id]) }}"
                                        class="site-nav-category-link">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>
    <div class="toast-container position-fixed top-0 end-0 p-3 site-toast-wrap">
        @if (session('dataSuccess'))
            <div class="toast align-items-center text-bg-success border-0 show mb-2 site-toast" role="alert"
                aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
                <div class="d-flex">
                    <div class="toast-body">{{ session('dataSuccess') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif
        @if (session('dataError') || $errors->any())
            <div class="toast align-items-center text-bg-danger border-0 show mb-2 site-toast" role="alert"
                aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body">{{ session('dataError') ?: $errors->first() }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>

    @guest('customer')
        <div class="modal fade site-auth-modal" id="siteAuthModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content site-auth-modal-content">
                    <div class="site-auth-modal-visual"
                        style="background-image: url({{ asset('storage/images/image_login.jpg') }});"></div>
                    <div class="site-auth-modal-form-wrap">
                        <button type="button" class="btn-close site-auth-modal-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                        <div class="site-auth-tabs mb-3">
                            <button type="button" class="site-auth-tab-btn is-active" data-auth-target="login">Đăng
                                nhập</button>
                            <button type="button" class="site-auth-tab-btn" data-auth-target="register">Đăng ký</button>
                        </div>

                        <div class="site-auth-panel is-active" data-auth-panel="login">
                            <h5 class="mb-3">Đăng nhập</h5>
                            <form action="{{ route('site.login.submit') }}" method="POST" class="d-grid gap-2">
                                @csrf
                                <input type="hidden" name="auth_form" value="login">
                                <input type="text" class="form-control" name="phone"
                                    value="{{ old('auth_form') === 'login' ? old('phone') : '' }}" placeholder="Số điện thoại"
                                    required>
                                <input type="password" class="form-control" name="password" placeholder="Password"
                                    required>
                                <button type="submit" class="btn btn-success w-100 mt-1">Đăng nhập</button>
                            </form>
                        </div>

                        <div class="site-auth-panel" data-auth-panel="register">
                            <h5 class="mb-3">Đăng ký</h5>
                            <form action="{{ route('site.register.submit') }}" method="POST" class="d-grid gap-2">
                                @csrf
                                <input type="hidden" name="auth_form" value="register">
                                <input type="text" class="form-control" name="full_name"
                                    value="{{ old('auth_form') === 'register' ? old('full_name') : '' }}"
                                    placeholder="Họ tên" required>
                                <input type="email" class="form-control" name="email"
                                    value="{{ old('auth_form') === 'register' ? old('email') : '' }}" placeholder="Email"
                                    required>
                                <input type="text" class="form-control" name="phone"
                                    value="{{ old('auth_form') === 'register' ? old('phone') : '' }}"
                                    placeholder="Số điện thoại">
                                <input type="password" class="form-control" name="password" placeholder="Mật khẩu"
                                    required>
                                <input type="password" class="form-control" name="password_confirmation"
                                    placeholder="Xác nhận mật khẩu" required>
                                <button type="submit" class="btn btn-success w-100 mt-1">Đăng ký</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endguest

    @include('site.component.footer')


    <div class="offcanvas offcanvas-end site-cart-offcanvas" tabindex="-1" id="siteCartPanel"
        data-bs-backdrop="static" aria-labelledby="siteCartPanelLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="siteCartPanelLabel">Giỏ hàng</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div id="siteCartPanelBody" class="site-cart-panel-body"></div>
            <div class="site-cart-panel-footer">
                <div class="site-cart-panel-total-wrap">
                    <span class="site-cart-panel-total-label">Tổng tiền:</span>
                    <strong id="siteCartTotalText" class="site-cart-panel-total-value">0 đ</strong>
                </div>
                <button id="siteCartCheckoutBtn" type="button" class="btn btn-secondary site-cart-panel-checkout"
                    disabled>
                    Thanh toán
                </button>
            </div>
        </div>
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
                toggleBtn.classList.remove('icon-dark', 'icon-light');
                toggleBtn.classList.add(isDark ? 'icon-dark' : 'icon-light');
                toggleBtn.innerHTML = isDark ?
                    `
                    <svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        fill="currentColor" viewBox="0 0 24 24" >
                        <path d="M20.71 13.51c-.78.23-1.58.35-2.38.35-4.52 0-8.2-3.68-8.2-8.2 0-.8.12-1.6.35-2.38.11-.35.01-.74-.25-1s-.64-.36-1-.25A10.17 10.17 0 0 0 2 11.8C2 17.42 6.57 22 12.2 22c4.53 0 8.45-2.91 9.76-7.24.11-.35.01-.74-.25-1s-.64-.36-1-.25M12.2 20C7.68 20 4 16.32 4 11.8a8.15 8.15 0 0 1 4.18-7.15c-.03.34-.05.68-.05 1.02 0 5.62 4.57 10.2 10.2 10.2.34 0 .68-.02 1.02-.05C17.93 18.38 15.23 20 12.2 20M16 8l.94-2.06L19 5l-2.06-.94L16 2l-.94 2.06L13 5l2.06.94zm4.25-.5-.55 1.2-1.2.55 1.2.55.55 1.2.55-1.2 1.2-.55-1.2-.55z"></path>
                    </svg>
                ` :
                    `
                    <svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        fill="currentColor" viewBox="0 0 24 24" >
                        <path d="M12 17.01c2.76 0 5.01-2.25 5.01-5.01S14.76 6.99 12 6.99 6.99 9.24 6.99 12s2.25 5.01 5.01 5.01M12 9c1.66 0 3.01 1.35 3.01 3.01s-1.35 3.01-3.01 3.01-3.01-1.35-3.01-3.01S10.34 9 12 9m1 10h-2v3h2zm0-17h-2v3h2zM2 11h3v2H2zm17 0h3v2h-3zM4.22 18.36l.71.71.71.71 1.06-1.06 1.06-1.06-.71-.71-.71-.71-1.06 1.06zM19.78 5.64l-.71-.71-.71-.71-1.06 1.06-1.06 1.06.71.71.71.71 1.06-1.06zm-12.02.7L6.7 5.28 5.64 4.22l-.71.71-.71.71L5.28 6.7l1.06 1.06.71-.71zm8.48 11.32 1.06 1.06 1.06 1.06.71-.71.71-.71-1.06-1.06-1.06-1.06-.71.71z"></path>
                    </svg>
                `;
            }
        };

        const menuToggle = document.getElementById('siteNavMenuToggle');
        const menuPanel = document.getElementById('siteNavMenuPanel');
        if (menuToggle && menuPanel) {
            menuToggle.addEventListener('click', () => {
                const open = menuPanel.hasAttribute('hidden');
                if (open) {
                    menuPanel.removeAttribute('hidden');
                    menuToggle.setAttribute('aria-expanded', 'true');
                } else {
                    menuPanel.setAttribute('hidden', '');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            });
        }

        const authModalEl = document.getElementById('siteAuthModal');
        const authTabButtons = Array.from(document.querySelectorAll('.site-auth-tab-btn'));
        const authPanels = Array.from(document.querySelectorAll('.site-auth-panel'));
        const authOpenButtons = Array.from(document.querySelectorAll('.js-open-auth-modal'));
        const authModalInstance = authModalEl && window.bootstrap?.Modal ? new bootstrap.Modal(authModalEl) : null;
        const initialAuthTab = @json(session('auth_tab') ?: old('auth_form') ?: request('auth'));
        const shouldOpenAuthModal = @json((bool) (session('auth_tab') || old('auth_form') || request('auth')));

        const setAuthTab = (tab) => {
            const next = tab === 'register' ? 'register' : 'login';
            authTabButtons.forEach((btn) => {
                const active = btn.getAttribute('data-auth-target') === next;
                btn.classList.toggle('is-active', active);
            });
            authPanels.forEach((panel) => {
                const active = panel.getAttribute('data-auth-panel') === next;
                panel.classList.toggle('is-active', active);
            });
        };

        authTabButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                setAuthTab(btn.getAttribute('data-auth-target'));
            });
        });

        authOpenButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                setAuthTab(btn.getAttribute('data-auth-tab'));
                authModalInstance?.show();
            });
        });

        if (initialAuthTab) {
            setAuthTab(initialAuthTab);
        }

        if (shouldOpenAuthModal) {
            authModalInstance?.show();
        }

        const cartPanelEl = document.getElementById('siteCartPanel');
        const cartPanelBody = document.getElementById('siteCartPanelBody');
        const cartTotalText = document.getElementById('siteCartTotalText');
        const cartCheckoutBtn = document.getElementById('siteCartCheckoutBtn');
        const cartDataUrl = @json(route('site.cart.index'));
        const cartItemUpdateRouteTemplate = @json(route('site.cart.items.update', ['id' => '__ID__']));
        const cartItemDeleteRouteTemplate = @json(route('site.cart.items.destroy', ['id' => '__ID__']));
        const cartCheckoutUrl = @json(route('site.checkout'));
        const csrfToken = @json(csrf_token());
        const qtyDebounceMap = new Map();

        const buildCartItemUrl = (template, itemId) => {
            const id = Number(itemId);
            if (!Number.isFinite(id) || id <= 0) {
                return null;
            }
            return template.replace('__ID__', String(id));
        };

        const formatCurrencyVnd = (value) => {
            const number = Number(value || 0);
            return `${number.toLocaleString('vi-VN')} đ`;
        };

        const escapeHtml = (unsafe) => {
            return String(unsafe ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        };

        const itemTemplate = (item) => {
            const id = Number(item.id);
            const outOfStock = Boolean(item.is_out_of_stock);
            const lineTotal = Number(item.price) * Number(item.quantity);
            const variantLine = item.variant_label ?
                `<p class="site-cart-item-variant small text-muted mb-1">${escapeHtml(item.variant_label)}</p>` :
                '';
            const pricing = item.pricing && typeof item.pricing === 'object' ? item.pricing : null;
            const pricingSplit =
                pricing && Number(pricing.variant_addon_unit) > 0 ?
                `<p class="small text-muted mb-1">Giá SP: ${formatCurrencyVnd(Number(pricing.product_base_unit || 0))} + Phiên bản: ${formatCurrencyVnd(Number(pricing.variant_addon_unit || 0))}</p>` :
                '';
            const image = item.image_url ?
                `<img src="${escapeHtml(item.image_url)}" alt="${escapeHtml(item.name)}" class="site-cart-item-image">` :
                `<div class="site-cart-item-image site-cart-item-image--placeholder">No image</div>`;

            const quantityBlock = outOfStock ?
                `<button class="site-cart-item-outofstock" type="button" disabled>Hết hàng</button>` :
                `
                    <div class="site-cart-item-qty" data-item-id="${id}">
                        <button type="button" class="site-cart-qty-btn" data-action="decrease" aria-label="Giảm số lượng">-</button>
                        <input type="number" class="site-cart-qty-input" min="1" value="${Number(item.quantity)}" data-item-id="${id}" />
                        <button type="button" class="site-cart-qty-btn" data-action="increase" aria-label="Tăng số lượng">+</button>
                    </div>
                `;

            return `
                <div class="site-cart-row">
                    <label class="site-cart-check-wrap">
                        <input type="checkbox" class="site-cart-check" data-item-id="${id}" ${outOfStock ? '' : 'checked'}>
                    </label>
                    <article class="site-cart-item" data-item-id="${id}" data-price="${Number(item.price)}" data-quantity="${Number(item.quantity)}">
                        <div class="site-cart-item-media">${image}</div>
                        <div class="site-cart-item-info">
                            <h6 class="site-cart-item-name">${escapeHtml(item.name || 'Sản phẩm')}</h6>
                            ${variantLine}
                            ${pricingSplit}
                            <p class="site-cart-item-meta">Kho: <strong>${Number(item.stock)}</strong></p>
                            <p class="site-cart-item-status ${outOfStock ? 'is-out' : 'is-in'}">${outOfStock ? 'Hết hàng' : 'Còn hàng'}</p>
                            <p class="site-cart-item-price">Giá: ${formatCurrencyVnd(Number(item.price))}</p>
                            <p class="site-cart-item-line-total" data-item-line="${id}">Tạm tính: ${formatCurrencyVnd(lineTotal)}</p>
                        </div>
                        <button type="button" class="site-cart-remove-btn" data-item-id="${id}" aria-label="Xóa sản phẩm">
                            <svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24" >
                                <path d="m7.76 14.83-2.83 2.83 1.41 1.41 2.83-2.83 2.12-2.12.71-.71.71.71 1.41 1.42 3.54 3.53 1.41-1.41-3.53-3.54-1.42-1.41-.71-.71 5.66-5.66-1.41-1.41L12 10.59 6.34 4.93 4.93 6.34 10.59 12l-.71.71z"></path>
                            </svg>
                        </button>
                        <div class="site-cart-item-qty-wrap">${quantityBlock}</div>
                    </article>
                </div>
            `;
        };

        const renderCart = (payload) => {
            const items = payload?.items ?? [];
            if (!cartPanelBody) {
                return;
            }

            if (items.length === 0) {
                cartPanelBody.innerHTML = `
                    <div class="site-cart-empty">
                        <p class="mb-2">Giỏ hàng đang trống.</p>
                        <a href="@js(route('site.home'))" class="btn btn-sm btn-outline-dark">Tiếp tục mua sắm</a>
                    </div>
                `;
                if (cartTotalText) {
                    cartTotalText.textContent = formatCurrencyVnd(0);
                }
                if (cartCheckoutBtn) {
                    cartCheckoutBtn.disabled = true;
                }
                return;
            }

            cartPanelBody.innerHTML = items.map(itemTemplate).join('');
            recalculateSelectedTotal();
        };

        const recalculateSelectedTotal = () => {
            if (!cartPanelBody || !cartTotalText || !cartCheckoutBtn) {
                return;
            }
            let selectedTotal = 0;
            let selectedCount = 0;
            cartPanelBody.querySelectorAll('.site-cart-item').forEach((el) => {
                const row = el.closest('.site-cart-row');
                const check = row ? row.querySelector('.site-cart-check') : null;
                if (!(check instanceof HTMLInputElement) || !check.checked) {
                    return;
                }
                const line = Number(el.dataset.price || 0) * Number(el.dataset.quantity || 0);
                selectedTotal += line;
                selectedCount += 1;
            });

            cartTotalText.textContent = formatCurrencyVnd(selectedTotal);
            cartCheckoutBtn.disabled = selectedCount < 1;
        };

        const patchCartItemQuantity = (itemId, qty) => {
            const updateUrl = buildCartItemUrl(cartItemUpdateRouteTemplate, itemId);
            if (!updateUrl) {
                return $.Deferred().reject({
                    responseJSON: {
                        message: 'ID sản phẩm giỏ không hợp lệ'
                    }
                }).promise();
            }
            return $.ajax({
                url: updateUrl,
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                data: {
                    quantity: qty,
                },
            });
        };

        const deleteCartItem = (itemId) => {
            const deleteUrl = buildCartItemUrl(cartItemDeleteRouteTemplate, itemId);
            if (!deleteUrl) {
                return $.Deferred().reject({
                    responseJSON: {
                        message: 'ID sản phẩm giỏ không hợp lệ'
                    }
                }).promise();
            }
            return $.ajax({
                url: deleteUrl,
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
        };

        const loadCartData = () => {
            if (!cartPanelBody) {
                return;
            }
            cartPanelBody.innerHTML = `<div class="site-cart-loading">Đang tải giỏ hàng...</div>`;
            $.ajax({
                url: cartDataUrl,
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).done((res) => {
                renderCart(res?.data ?? {
                    items: []
                });
            }).fail(() => {
                cartPanelBody.innerHTML = `<div class="site-cart-error">Không tải được dữ liệu giỏ hàng.</div>`;
            });
        };

        const debounceQuantityUpdate = (itemId, quantity) => {
            if (qtyDebounceMap.has(itemId)) {
                clearTimeout(qtyDebounceMap.get(itemId));
            }
            const timer = setTimeout(() => {
                patchCartItemQuantity(itemId, quantity)
                    .done((res) => {
                        renderCart(res?.data ?? {
                            items: []
                        });
                    })
                    .fail((xhr) => {
                        const message = xhr?.responseJSON?.message || 'Cập nhật giỏ hàng thất bại';
                        if (window.Alert?.error) {
                            Alert.error(message);
                        }
                        loadCartData();
                    });
            }, 2000);
            qtyDebounceMap.set(itemId, timer);
        };

        if (cartPanelEl) {
            cartPanelEl.addEventListener('show.bs.offcanvas', () => {
                loadCartData();
            });
        }

        if (cartPanelBody) {
            cartPanelBody.addEventListener('change', (event) => {
                if (!(event.target instanceof Element)) {
                    return;
                }
                if (event.target.classList.contains('site-cart-check')) {
                    recalculateSelectedTotal();
                    return;
                }
                if (event.target.classList.contains('site-cart-qty-input')) {
                    const input = event.target;
                    const itemId = Number(input.getAttribute('data-item-id'));
                    const nextQty = Math.max(1, Number(input.value || 1));
                    input.value = String(nextQty);
                    const itemRoot = cartPanelBody.querySelector(`.site-cart-item[data-item-id="${itemId}"]`);
                    if (itemRoot instanceof HTMLElement) {
                        itemRoot.dataset.quantity = String(nextQty);
                        const lineEl = itemRoot.querySelector(`[data-item-line="${itemId}"]`);
                        if (lineEl) {
                            const line = Number(itemRoot.dataset.price || 0) * nextQty;
                            lineEl.textContent = `Tạm tính: ${formatCurrencyVnd(line)}`;
                        }
                    }
                    recalculateSelectedTotal();
                    debounceQuantityUpdate(itemId, nextQty);
                }
            });

            cartPanelBody.addEventListener('click', (event) => {
                if (!(event.target instanceof Element)) {
                    return;
                }

                const removeBtn = event.target.closest('.site-cart-remove-btn');
                if (removeBtn) {
                    const itemId = Number(removeBtn.getAttribute('data-item-id'));
                    const removeBtnOriginalHtml = removeBtn.innerHTML;
                    removeBtn.disabled = true;
                    removeBtn.classList.add('is-loading');
                    removeBtn.innerHTML =
                        `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`;

                    deleteCartItem(itemId)
                        .done((res) => renderCart(res?.data ?? {
                            items: []
                        }))
                        .fail(() => {
                            removeBtn.disabled = false;
                            removeBtn.classList.remove('is-loading');
                            removeBtn.innerHTML = removeBtnOriginalHtml;
                            if (window.Alert?.error) {
                                Alert.error('Xóa khỏi giỏ hàng thất bại');
                            }
                        });
                    return;
                }

                const qtyBtn = event.target.closest('.site-cart-qty-btn');
                if (qtyBtn) {
                    const qtyWrap = qtyBtn.closest('.site-cart-item-qty');
                    if (!qtyWrap) {
                        return;
                    }
                    const itemId = Number(qtyWrap.getAttribute('data-item-id'));
                    const input = qtyWrap.querySelector('.site-cart-qty-input');
                    if (!(input instanceof HTMLInputElement)) {
                        return;
                    }
                    const action = qtyBtn.getAttribute('data-action');
                    const current = Number(input.value || 1);
                    const nextQty = action === 'decrease' ? Math.max(1, current - 1) : current + 1;
                    input.value = String(nextQty);
                    input.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }
            });
        }

        if (cartCheckoutBtn) {
            cartCheckoutBtn.addEventListener('click', () => {
                if (!cartPanelBody) {
                    return;
                }
                const selectedIds = [];
                cartPanelBody.querySelectorAll('.site-cart-check').forEach((el) => {
                    if (el instanceof HTMLInputElement && el.checked) {
                        selectedIds.push(Number(el.dataset.itemId));
                    }
                });
                if (selectedIds.length < 1) {
                    if (window.Alert?.warning) {
                        Alert.warning('Vui lòng chọn ít nhất 1 sản phẩm để thanh toán');
                    }
                    return;
                }
                window.location.href = `${cartCheckoutUrl}?selected_items=${selectedIds.join(',')}`;
            });
        }

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
