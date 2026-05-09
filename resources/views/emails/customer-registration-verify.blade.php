<x-mail::message>
Xin chào **{{ $customer->full_name }}**,

Cảm ơn bạn đã đăng ký tại **{{ config('app.name') }}**. Để kích hoạt tài khoản và bảo vệ thông tin của bạn, vui lòng xác nhận địa chỉ email bằng nút bên dưới.

Đây là bước cuối cùng của quá trình đăng ký. Sau khi xác thực, bạn có thể đăng nhập bằng số điện thoại và mật khẩu đã đặt.

<x-mail::button :url="$verifyUrl" color="success">
    Xác thực tài khoản
</x-mail::button>

Nếu bạn không thực hiện đăng ký này, bạn có thể bỏ qua email — tài khoản sẽ không được kích hoạt.

Trân trọng,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Nếu nút không hoạt động, sao chép và dán liên kết sau vào trình duyệt:<br>
<span class="break-all">{{ $verifyUrl }}</span>
</x-mail::subcopy>
</x-mail::message>
