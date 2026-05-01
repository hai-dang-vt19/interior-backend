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
        $customer = $this->siteAuthService->attemptLogin($credentials['email'], $credentials['password']);
        if (!$customer) {
            return redirect()->back()
                ->with('dataError', 'Email hoặc mật khẩu không đúng')
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
        $customer = $this->siteAuthService->registerCustomer($payload);

        Auth::guard('customer')->login($customer);
        return redirect()->route('site.home')->with('dataSuccess', 'Đăng ký tài khoản thành công');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('site.home')->with('dataSuccess', 'Đã đăng xuất');
    }
}
