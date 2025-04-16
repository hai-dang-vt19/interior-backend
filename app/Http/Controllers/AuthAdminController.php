<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Http\Requests\LoginRequest;
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

        if ($user->role->value != UserRole::ADMIN->value) {
            return response()->json([
                'error' => [
                    'msg' => 'Bạn không có quyền truy cập vào trang quản trị'
                ]
            ], 403);
        }

        Auth::login($user);
        return $this->sendRedirectAjax('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return $this->sendSuccess('admin.login');
    }
}
