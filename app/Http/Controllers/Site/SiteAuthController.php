<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteForgotPasswordRequest;
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
            return $this->respondSiteAuthFailure(
                $request,
                'Số điện thoại hoặc mật khẩu không đúng',
                ['phone' => ['Số điện thoại hoặc mật khẩu không đúng.']]
            );
        }

        if (!$customer->hasVerifiedEmail()) {
            return $this->respondSiteAuthFailure(
                $request,
                'Email chưa được xác thực. Vui lòng kiểm tra hộp thư đến hoặc mục spam.',
                ['phone' => ['Email chưa được xác thực. Vui lòng kiểm tra hộp thư đến hoặc mục spam.']]
            );
        }

        Auth::guard('customer')->login($customer);

        if ($request->expectsJson()) {
            $request->session()->flash('dataSuccess', 'Đăng nhập thành công');

            return response()->json([
                'redirect' => route('site.home'),
                'message' => 'Đăng nhập thành công',
            ]);
        }

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

        $successMessage = 'Đăng ký thành công. Vui lòng mở email và nhấn nút “Xác thực tài khoản” để hoàn tất trước khi đăng nhập.';

        if ($request->expectsJson()) {
            $request->session()->flash('dataSuccess', $successMessage);

            return response()->json([
                'redirect' => route('site.home'),
                'message' => $successMessage,
            ]);
        }

        return redirect()->route('site.home')->with('dataSuccess', $successMessage);
    }

    public function forgotPassword(SiteForgotPasswordRequest $request)
    {
        $this->siteAuthService->resetPasswordByEmail($request->validated('email'));

        $message = 'Nếu email đã đăng ký, mật khẩu mới đã được gửi vào hộp thư của bạn.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('site.home', ['auth' => 'login'])
            ->with('dataSuccess', $message);
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    private function respondSiteAuthFailure(
        Request $request,
        string $message,
        array $errors
    ): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => $errors,
            ], 422);
        }

        return redirect()->back()
            ->with('dataError', $message)
            ->with('auth_tab', 'login')
            ->withInput();
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
