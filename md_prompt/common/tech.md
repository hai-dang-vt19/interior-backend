### Kiểm tra và tuần thủ theo đùng ngôn ngữ và phiên bản:
## Backend
- Laravel: 12.0
- php 8.2
- axios: 1.8.2
## Frontend
- Blade
- Thư viện hỗ trợ:
    + SASS
    + popperjs/core: 2.11.8
    + bootstrap: 5.3.5
    + flatpickr: 4.6.13
    + jquery: 3.7.1 (ajax)
    + sweetalert2: 11.18.0
    + Swiper: 12
## khác
- Docker
- Vite
## Design partten
- CURL(router) --call--> controller --call--> service --call--> repository interface --call--> repository --call--> model
# Lưu ý
- Controller: lấy kết quả tra về tử service gửi về Mã (200, 401, ...) + Message tương ứng (xem thêm app\Http\Controllers\BaseController.php)
- Service: xử lý logic tổng hợp kết quả trả về cho các controller khác nhau sử dụng (Xem thêm app\Http\Controllers\BaseController.php)
- Repository interface: khởi tạo function để service gọi đên repository
- Repository: khởi tạo query đến database tương ứng model
- Model: thông tin của các bảng và function cần thiết để sử dụng
- Thêm comment tiếng việt giải thích mục địch bên trên function
