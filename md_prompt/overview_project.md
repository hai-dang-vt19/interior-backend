###### Hệ thống quản lý và bán hàng sản phẩm nội thất

> **URL đầy đủ (mặc định dev — `USE_DOMAIN_ROUTING=true`):**
> - **Admin:** `http://chungsi.admin.localhost` (theo `ADMIN_DOMAIN` trong `.env`, bỏ `http://` nếu chỉ ghi hostname).
> - **Site khách:** `http://chungsi.user.localhost` (theo `CUSTOMER_DOMAIN`).
>
> **`USE_DOMAIN_ROUTING=false`:** thay host bằng `http://localhost` (hoặc `APP_URL`) — admin vẫn có prefix `/admin/...`, site vẫn là `/`, `/login`, …
>
> `{id}`, `{imageId}`, `{addressId}`, `{returnId}` = thay bằng ID thật. Tên route: `route('...')` hoặc `php artisan route:list`.

##### Các chức năng chính

* **Xóa mềm (soft delete)** — dùng trên các module có `deleted_at` (sản phẩm, danh mục, đơn hàng, khách hàng, nhân viên).

---

- **Quản lý sản phẩm (CRUD)** — danh sách: `http://chungsi.admin.localhost/admin/product` — route `admin.product.index`

    | Chức năng | URL đầy đủ (GET mở trình duyệt; POST/PATCH/DELETE qua form/API) | Route |
    |-----------|----------------------------------------------------------------|-------|
    | ➕ Thêm sản phẩm mới | `http://chungsi.admin.localhost/admin/product` (POST) | `admin.product.store` |
    | ✏️ Cập nhật thông tin | `http://chungsi.admin.localhost/admin/product/{id}` (GET form · POST lưu) | `admin.product.edit` · `admin.product.update` |
    | 🗑️ Xóa mềm sản phẩm | `http://chungsi.admin.localhost/admin/product/destroy/{id}` (DELETE) | `admin.product.destroy` |
    | 🔍 Tìm kiếm & lọc | `http://chungsi.admin.localhost/admin/product` (+ query string trên form) | `admin.product.index` |
    | 📷 Quản lý hình ảnh | `http://chungsi.admin.localhost/admin/product/{id}/images` (GET · POST) · DELETE `http://chungsi.admin.localhost/admin/product/{id}/images/{imageId}` | `admin.product.images` · `images.store` · `images.destroy` |
    | 🏷️ Phân loại danh mục | `http://chungsi.admin.localhost/admin/category` | `admin.category.*` |
    | 📦 Quản lý tồn kho | `http://chungsi.admin.localhost/admin/product/{id}/inventory` (GET · POST điều chỉnh) | `admin.product.inventory` · `inventory.adjust` |
    | 🔄 Khôi phục sản phẩm | `http://chungsi.admin.localhost/admin/product/restore/{id}` (PATCH) | `admin.product.restore` |
    | 💰 Quản lý giá & khuyến mãi | `http://chungsi.admin.localhost/admin/product/{id}` (GET/POST) | `admin.product.edit` / `admin.product.update` |
    | 📋 Xem lịch sử thay đổi (tồn kho) | `http://chungsi.admin.localhost/admin/product/{id}/inventory` | `admin.product.inventory` |
    | Xóa vĩnh viễn | `http://chungsi.admin.localhost/admin/product/force-destroy/{id}` (DELETE) | `admin.product.force-destroy` |

---

- **Quản lý đơn hàng (CRUD)** — danh sách: `http://chungsi.admin.localhost/admin/order` — route `admin.order.index`

    | Chức năng | URL đầy đủ | Route |
    |-----------|------------|-------|
    | ➕ Tạo đơn hàng mới | `http://chungsi.admin.localhost/admin/order` (POST) | `admin.order.store` |
    | ✏️ Cập nhật đơn hàng | `http://chungsi.admin.localhost/admin/order/{id}` (GET · POST) | `admin.order.edit` · `admin.order.update` |
    | 🗑️ Hủy đơn (soft delete) | `http://chungsi.admin.localhost/admin/order/destroy/{id}` (DELETE) | `admin.order.destroy` |
    | 🔍 Tìm kiếm & lọc đơn | `http://chungsi.admin.localhost/admin/order` | `admin.order.index` |
    | 🚚 / 💳 Trạng thái & thanh toán | Form trên `http://chungsi.admin.localhost/admin/order/{id}` · chi tiết `http://chungsi.admin.localhost/admin/order/{id}/show` | `admin.order.update` · `admin.order.show` |
    | 🏠 Quản lý giao hàng | `http://chungsi.admin.localhost/admin/order/{id}/shipping` (PATCH) | `admin.order.shipping.update` |
    | 🔄 Hoàn trả / đổi hàng | POST `http://chungsi.admin.localhost/admin/order/{id}/return-request` · PATCH `http://chungsi.admin.localhost/admin/order/{id}/return-request/{returnId}` | `admin.order.return.store` · `admin.order.return.update` |
    | 🖨️ Xuất hóa đơn / PDF | `http://chungsi.admin.localhost/admin/order/{id}/invoice` | `admin.order.invoice` |
    | 📋 Lịch sử đơn hàng | `http://chungsi.admin.localhost/admin/order/{id}/show` | `admin.order.show` |
    | Khôi phục / xóa vĩnh viễn | `http://chungsi.admin.localhost/admin/order/restore/{id}` (PATCH) · `.../force-destroy/{id}` (DELETE) | `admin.order.restore` · `admin.order.force-destroy` |

---

- **Quản lý Khách hàng (CRUD)** — danh sách: `http://chungsi.admin.localhost/admin/customer` — route `admin.customer.index`

    | Chức năng | URL đầy đủ | Route |
    |-----------|------------|-------|
    | ➕ Thêm khách hàng mới | `http://chungsi.admin.localhost/admin/customer` (POST) | `admin.customer.store` |
    | ✏️ Cập nhật thông tin | `http://chungsi.admin.localhost/admin/customer/{id}` (GET · POST) | `admin.customer.edit` · `admin.customer.update` |
    | 🗑️ Vô hiệu hóa (soft delete) | `http://chungsi.admin.localhost/admin/customer/destroy/{id}` (DELETE) | `admin.customer.destroy` |
    | 🔍 Tìm kiếm khách hàng | `http://chungsi.admin.localhost/admin/customer` | `admin.customer.index` |
    | 📜 Lịch sử mua hàng | `http://chungsi.admin.localhost/admin/order` (lọc theo KH) hoặc chi tiết đơn | `admin.order.index` |
    | ⭐ / 🎁 Hạng & điểm thưởng | Form tại `http://chungsi.admin.localhost/admin/customer` / `.../customer/{id}` | `admin.customer.store` / `update` |
    | 🔄 Khôi phục | `http://chungsi.admin.localhost/admin/customer/restore/{id}` (PATCH) | `admin.customer.restore` |
    | 📍 Địa chỉ | `http://chungsi.admin.localhost/admin/customer/{id}/profile` · POST `.../address` · DELETE `.../address/{addressId}` | `admin.customer.profile` · `address.store` · `address.destroy` |
    | 🔔 Liên hệ | `http://chungsi.admin.localhost/admin/customer/{id}/contact` (POST) | `admin.customer.contact.store` |
    | Xóa vĩnh viễn | `http://chungsi.admin.localhost/admin/customer/force-destroy/{id}` (DELETE) | `admin.customer.force-destroy` |

---

- **Quản lý nhân viên (CRUD)** — `http://chungsi.admin.localhost/admin/staff` — chỉ **ADMIN**

    | Chức năng | URL đầy đủ | Route |
    |-----------|------------|-------|
    | ➕ Thêm nhân viên | `http://chungsi.admin.localhost/admin/staff` (POST) | `admin.staff.store` |
    | ✏️ Cập nhật | `http://chungsi.admin.localhost/admin/staff/{id}` (GET · POST) | `admin.staff.edit` · `admin.staff.update` |
    | 🗑️ Vô hiệu hóa | `http://chungsi.admin.localhost/admin/staff/destroy/{id}` (DELETE) | `admin.staff.destroy` |
    | 🔄 Khôi phục / xóa vĩnh viễn | `http://chungsi.admin.localhost/admin/staff/restore/{id}` (PATCH) · `.../force-destroy/{id}` (DELETE) | `admin.staff.restore` · `admin.staff.force-destroy` |

---

##### Chức năng hỗ trợ chung

- **Xác thực & phân quyền**

    | Chức năng | URL đầy đủ | Route |
    |-----------|------------|-------|
    | 🔐 Đăng nhập (admin) | `http://chungsi.admin.localhost/admin/login` (GET · POST) | `admin.login` · `admin.login.submit` |
    | 🔐 Đăng ký staff | `http://chungsi.admin.localhost/admin/register` (GET · POST) | `admin.register` · `admin.register.submit` |
    | 🔐 Đăng nhập / đăng ký (khách) | `http://chungsi.user.localhost/login` · `http://chungsi.user.localhost/register` | `site.login` · `site.register` |
    | 👥 Phân quyền theo vai trò | Cấu hình middleware `role:...` trong `routes/admin.php` (không có URL riêng) | — |
    | 🔑 Đổi mật khẩu | `http://chungsi.admin.localhost/admin/change-password` (GET · POST) | `admin.change-password` · `admin.change-password.submit` |
    | 📝 Nhật ký hoạt động (auth) | `http://chungsi.admin.localhost/admin/auth-activity-logs` | `admin.auth-activity-logs` |
    | Đăng xuất admin | `http://chungsi.admin.localhost/admin/logout` (POST) | `admin.logout` |

---

- **Báo cáo & thống kê**

    | Chức năng | URL đầy đủ | Route |
    |-----------|------------|-------|
    | 📊 Dashboard / doanh thu theo kỳ | `http://chungsi.admin.localhost/admin` | `admin.dashboard` |
    | 🏆 Sản phẩm bán chạy | Cùng trang `http://chungsi.admin.localhost/admin` | `admin.dashboard` |
    | 👤 Khách hàng tiềm năng | Cùng trang `http://chungsi.admin.localhost/admin` | `admin.dashboard` |
    | 📥 Xuất báo cáo Excel | `http://chungsi.admin.localhost/admin/dashboard/export-revenue` (có thể thêm `?dateFrom=...`) | `admin.dashboard.export-revenue` |

---

##### Site khách hàng (mua hàng online) — host: `http://chungsi.user.localhost`

| Mô tả | URL đầy đủ | Route |
|-------|------------|-------|
| Trang chủ / danh sách SP | `http://chungsi.user.localhost/` | `site.home` |
| Chi tiết sản phẩm | `http://chungsi.user.localhost/products/{id}` | `site.products.show` |
| Giỏ hàng | `http://chungsi.user.localhost/cart` | `site.cart.index` |
| Thêm/sửa/xóa dòng giỏ | POST `http://chungsi.user.localhost/cart/items` · PATCH/DELETE `http://chungsi.user.localhost/cart/items/{id}` | `site.cart.items.store` · `update` · `destroy` |
| Thanh toán | `http://chungsi.user.localhost/checkout` (GET · POST) | `site.checkout` · `site.checkout.submit` |
| Đơn hàng của tôi | `http://chungsi.user.localhost/orders` · `http://chungsi.user.localhost/orders/{id}` | `site.orders.index` · `site.orders.show` |
| Đăng xuất khách | `http://chungsi.user.localhost/logout` (POST) | `site.logout` |
