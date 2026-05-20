<nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('admin.home') }}">Chung Si Interior</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText"
            aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            @php($currentUser = auth()->user())
            @php($isAdmin = $currentUser && $currentUser->role === \App\Enums\UserRole::ADMIN)
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @if ($isAdmin)
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.dashboard*') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('admin.product.*') ? 'active' : '' }}"
                        href="{{ route('admin.product.index') }}">Sản phẩm</a>
                </li>
                @if ($isAdmin)
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.category.*') ? 'active' : '' }}"
                            href="{{ route('admin.category.index') }}">Danh mục</a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('admin.customer.*') ? 'active' : '' }}"
                        href="{{ route('admin.customer.index') }}">Khách hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('admin.order.*') ? 'active' : '' }}"
                        href="{{ route('admin.order.index') }}">Đơn hàng</a>
                </li>
                @if ($isAdmin)
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.staff.*') ? 'active' : '' }}"
                            href="{{ route('admin.staff.index') }}">Nhân viên</a>
                    </li>
                @endif
            </ul>
            @auth
                <div class="order-pending-notify-wrap me-3 d-flex align-items-center">
                    <button type="button" class="btn btn-link text-white text-decoration-none p-1 position-relative"
                        id="adminPendingOrdersBtn" data-bs-toggle="modal" data-bs-target="#adminPendingOrdersModal"
                        title="Đơn chờ xác nhận" aria-label="Đơn chờ xác nhận">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor"
                            viewBox="0 0 16 16" aria-hidden="true">
                            <path
                                d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm.995-14.901a1 1 0 1 0-1.99 0A5.002 5.002 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901z" />
                        </svg>
                        <span
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
                            id="adminPendingOrdersBadge">0</span>
                    </button>
                </div>
            @endauth
            <span class="navbar-text">
                @if ($isAdmin)
                    <a href="{{ route('admin.auth-activity-logs') }}"
                        class="btn btn-sm btn-outline-secondary text-white">Nhật ký auth</a>
                @endif
                <span class="admin-nav-user">{{ $currentUser?->full_name }}
                    ({{ $currentUser?->role?->label() }})</span>
                <a href="{{ route('admin.change-password') }}"
                    class="btn btn-sm btn-outline-primary text-white">Đổi mật khẩu</a>
                <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger" title="Đăng xuất">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" aria-hidden="true">
                            <path
                                d="M569 337C578.4 327.6 578.4 312.4 569 303.1L425 159C418.1 152.1 407.8 150.1 398.8 153.8C389.8 157.5 384 166.3 384 176L384 256L272 256C245.5 256 224 277.5 224 304L224 336C224 362.5 245.5 384 272 384L384 384L384 464C384 473.7 389.8 482.5 398.8 486.2C407.8 489.9 418.1 487.9 425 481L569 337zM224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L160 96C107 96 64 139 64 192L64 448C64 501 107 544 160 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480C142.3 480 128 465.7 128 448L128 192C128 174.3 142.3 160 160 160L224 160z" />
                        </svg>
                        <span class="d-none d-md-inline">Đăng xuất</span>
                    </button>
                </form>
            </span>
        </div>
    </div>
</nav>
