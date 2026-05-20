<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AdminAuthService;
use Illuminate\Support\Facades\Auth;

class AuthAdminController extends BaseController
{
    public function __construct(
        private AdminAuthService $adminAuthService
    ) {}

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $this->adminAuthService->registerStaff(
            $request->validated(),
            (string) $request->ip(),
            (string) $request->userAgent()
        );

        return redirect()->route('admin.login')->with('dataSuccess', 'Đăng ký thành công, vui lòng đăng nhập');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $result = $this->adminAuthService->attemptLogin(
            $credentials['phone'],
            $credentials['password'],
            (string) $request->ip(),
            (string) $request->userAgent()
        );

        if (!$result['ok']) {
            return response()->json([
                'error' => [
                    'msg' => $result['message'],
                ]
            ], (int) $result['code']);
        }

        $user = $result['user'];
        Auth::login($user);

        $landingRoute = $user->role instanceof UserRole
            ? $user->role->defaultLandingRoute()
            : 'admin.order.index';

        return $this->sendRedirectAjax($landingRoute);
    }

    public function logout(Request $request)
    {
        $userId = (int) Auth::id();
        $this->adminAuthService->writeLogoutLog($userId, (string) $request->ip(), (string) $request->userAgent());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('dataSuccess', 'Đã đăng xuất');
    }

    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function activityLogs(Request $request)
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $logs = $this->adminAuthService->getActivityLogs($keyword);

        return view('auth.activity-log', compact('logs', 'keyword'));
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->back()->with('dataError', 'Không tìm thấy tài khoản đăng nhập');
        }

        $changed = $this->adminAuthService->changePassword(
            $user,
            (string) $request->input('current_password'),
            (string) $request->input('new_password'),
            (string) $request->ip(),
            (string) $request->userAgent()
        );

        if (!$changed) {
            return redirect()->back()->with('dataError', 'Mật khẩu hiện tại không đúng');
        }

        return redirect()->back()->with('dataSuccess', 'Đổi mật khẩu thành công');
    }
}
