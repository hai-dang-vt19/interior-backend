<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteLoginRequest;
use App\Http\Requests\SiteRegisterRequest;
use App\Services\SiteAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteAuthController extends Controller
{
    public function __construct(
        private SiteAuthService $siteAuthService
    ) {}

    public function showLogin()
    {
        return redirect()->route('site.home', ['auth' => 'login']);
    }

    public function login(SiteLoginRequest $request)
    {
        $credentials = $request->validated();
        $customer = $this->siteAuthService->attemptLogin($credentials['phone'], $credentials['password']);
        if (!$customer) {
            return redirect()->back()
                ->with('dataError', 'Số điện thoại hoặc mật khẩu không đúng')
                ->with('auth_tab', 'login')
                ->withInput();
        }

        if (!$customer->hasVerifiedEmail()) {
            return redirect()->back()
                ->with('dataError', 'Email chưa được xác thực. Vui lòng kiểm tra hộp thư đến hoặc mục spam.')
                ->with('auth_tab', 'login')
                ->withInput();
        }

        Auth::guard('customer')->login($customer);
        return redirect()->route('site.home')->with('dataSuccess', 'Đăng nhập thành công');
    }

    public function showRegister()
    {
        return redirect()->route('site.home', ['auth' => 'register']);
    }

    public function register(SiteRegisterRequest $request)
    {
        $payload = $request->validated();
        $this->siteAuthService->registerCustomer($payload);

        return redirect()->route('site.home')->with(
            'dataSuccess',
            'Đăng ký thành công. Vui lòng mở email và nhấn nút “Xác thực tài khoản” để hoàn tất trước khi đăng nhập.'
        );
    }

    /**
     * Trang kết quả sau khi người dùng nhấn liên kết xác thực trong email
     */
    public function verifyRegistrationEmail(Request $request)
    {
        $token = (string) $request->query('token', '');
        $result = $this->siteAuthService->verifyRegistrationEmail($token);

        return view('site.auth.register-verify-result', ['status' => $result['status']]);
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('site.home')->with('dataSuccess', 'Đã đăng xuất');
    }
}
