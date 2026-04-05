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
              <a class="nav-link {{ Route::is('admin.product-review.*') ? 'active' : '' }}" href="{{ route('admin.product-review.index') }}">Đánh giá SP</a>
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
        <span class="navbar-text">
          @if ($isAdmin)
            <a href="{{ route('admin.auth-activity-logs') }}" class="btn btn-sm btn-outline-secondary me-2">Nhật ký auth</a>
          @endif
          <span class="me-2">{{ $currentUser?->full_name }} ({{ $currentUser?->role?->label() }})</span>
          <a href="{{ route('admin.change-password') }}" class="btn btn-sm btn-outline-primary me-2">Đổi mật khẩu</a>
          <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-sm btn-danger">Logout</button>
          </form>
        </span>
      </div>
    </div>
</nav>
