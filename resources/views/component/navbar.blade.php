<nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid">
      <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Chung Si Interior</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarText">
        @php($currentUser = auth()->user())
        @php($isAdmin = $currentUser && $currentUser->role && $currentUser->role->name === 'ADMIN')
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link {{ Route::is('admin.dashboard*') ? 'active' : '' }}" aria-current="page" href="{{ route('admin.dashboard') }}">Dashboard</a>
          </li>
          @if ($isAdmin)
            <li class="nav-item">
              <a class="nav-link {{ Route::is('admin.product.*') ? 'active' : '' }}" href="{{ route('admin.product.index') }}">Sản phẩm</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ Route::is('admin.category.*') ? 'active' : '' }}" href="{{ route('admin.category.index') }}">Danh mục</a>
            </li>
          @endif
          <li class="nav-item">
            <a class="nav-link {{ Route::is('admin.customer.*') ? 'active' : '' }}" href="{{ route('admin.customer.index') }}">Khách hàng</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ Route::is('admin.order.*') ? 'active' : '' }}" href="{{ route('admin.order.index') }}">Đơn hàng</a>
          </li>
          @if ($isAdmin)
            <li class="nav-item">
              <a class="nav-link {{ Route::is('admin.staff.*') ? 'active' : '' }}" href="{{ route('admin.staff.index') }}">Nhân viên</a>
            </li>
          @endif
        </ul>
        @auth
            <div class="order-pending-notify-wrap me-3 d-flex align-items-center">
                <button type="button" class="btn btn-link text-white text-decoration-none p-1 position-relative"
                    id="adminPendingOrdersBtn" data-bs-toggle="modal" data-bs-target="#adminPendingOrdersModal"
                    title="Đơn chờ xác nhận" aria-label="Đơn chờ xác nhận">
                    {{-- Thay thế SVG dưới bằng icon thông báo của bạn --}}
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
            <a href="{{ route('admin.auth-activity-logs') }}" class="btn btn-sm btn-outline-secondary text-white me-2">Nhật ký auth</a>
          @endif
          <span class="me-2 text-white">{{ $currentUser?->full_name }} ({{ $currentUser?->role?->label() }})</span>
          <a href="{{ route('admin.change-password') }}" class="btn btn-sm btn-outline-primary text-white me-2">Đổi mật khẩu</a>
          <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-sm btn-danger">Logout</button>
          </form>
        </span>
      </div>
    </div>
</nav>
