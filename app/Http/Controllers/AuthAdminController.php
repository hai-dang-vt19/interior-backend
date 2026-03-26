<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\AuthActivityLog;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthAdminController extends BaseController
{
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
        $user = User::query()->create([
            'full_name' => $request->input('full_name'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => UserRole::STAFF,
        ]);

        AuthActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => 'register',
            'description' => 'Đăng ký tài khoản staff',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return redirect()->route('admin.login')->with('dataSuccess', 'Đăng ký thành công, vui lòng đăng nhập');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $user = User::where('phone', $credentials['phone'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'error' => [
                    'msg' => 'Số điện thoại hoặc mật khẩu không đúng'
                ]
            ], 401);
        }

        if (!in_array($user->role->value, [UserRole::ADMIN->value, UserRole::STAFF->value], true)) {
            return response()->json([
                'error' => [
                    'msg' => 'Bạn không có quyền truy cập vào trang quản trị'
                ]
            ], 403);
        }

        Auth::login($user);

        AuthActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'Đăng nhập hệ thống',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return $this->sendRedirectAjax('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        AuthActivityLog::query()->create([
            'user_id' => $userId,
            'action' => 'logout',
            'description' => 'Đăng xuất hệ thống',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return $this->sendSuccess('admin.login');
    }

    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function activityLogs(Request $request)
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $logs = AuthActivityLog::query()
            ->with('user')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('action', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            })
            ->latest('id')
            ->paginate(20)
            ->appends($request->query());

        return view('auth.activity-log', compact('logs', 'keyword'));
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();
        if (!$user || !Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->back()->with('dataError', 'Mật khẩu hiện tại không đúng');
        }

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        AuthActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => 'change_password',
            'description' => 'Đổi mật khẩu tài khoản',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return redirect()->back()->with('dataSuccess', 'Đổi mật khẩu thành công');
    }
}
