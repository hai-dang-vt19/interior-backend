<x-mail::message>
Xin chào **{{ $customer->full_name }}**,

Tài khoản khách hàng của bạn đã được tạo tại **{{ config('app.name') }}** bởi đội ngũ quản trị.

Bạn có thể đăng nhập website bằng thông tin sau:

<x-mail::panel>
**Số điện thoại:** {{ $customer->phone ?: '—' }}<br>
**Mật khẩu tạm:** {{ $plainPassword }}
</x-mail::panel>

Vui lòng đăng nhập và **đổi mật khẩu** trong mục tài khoản sau lần đăng nhập đầu tiên.

<x-mail::button :url="$loginUrl" color="success">
    Đăng nhập
</x-mail::button>

Nếu bạn không yêu cầu tạo tài khoản, hãy liên hệ với chúng tôi ngay.

Trân trọng,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Nếu nút không hoạt động, sao chép và dán liên kết sau vào trình duyệt:<br>
<span class="break-all">{{ $loginUrl }}</span>
</x-mail::subcopy>
</x-mail::message>
