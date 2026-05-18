<x-mail::message>
Xin chào **{{ $customer->full_name }}**,

Bạn (hoặc ai đó) đã yêu cầu đặt lại mật khẩu tại **{{ config('app.name') }}**.

Mật khẩu mới của bạn:

<x-mail::panel>
**{{ $newPassword }}**
</x-mail::panel>

Vui lòng đăng nhập bằng **số điện thoại** đã đăng ký và mật khẩu trên, sau đó đổi mật khẩu trong tài khoản nếu cần.

Nếu bạn không yêu cầu đặt lại mật khẩu, hãy liên hệ với chúng tôi ngay.

Trân trọng,<br>
{{ config('app.name') }}
</x-mail::message>
