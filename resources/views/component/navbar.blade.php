<nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid">
      <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Chung Si Interior</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarText">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Sản phẩm</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Khách hàng</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Đơn hàng</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Đơn hàng</a>
          </li>
        </ul>
        <span class="navbar-text">
          <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger">Logout</button>
          </form>
        </span>
      </div>
    </div>
</nav>