<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteLoginRequest;
use App\Http\Requests\SiteRegisterRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SiteAuthController extends Controller
{
    public function showLogin()
    {
        return view('site.auth.login');
    }

    public function login(SiteLoginRequest $request)
    {
        $credentials = $request->validated();
        $customer = Customer::query()->where('email', $credentials['email'])->first();

        if (!$customer || !Hash::check($credentials['password'], $customer->password)) {
            return redirect()->back()->with('dataError', 'Email hoặc mật khẩu không đúng')->withInput();
        }

        Auth::guard('customer')->login($customer);
        return redirect()->route('site.home')->with('dataSuccess', 'Đăng nhập thành công');
    }

    public function showRegister()
    {
        return view('site.auth.register');
    }

    public function register(SiteRegisterRequest $request)
    {
        $payload = $request->validated();
        $customer = Customer::query()->create([
            'full_name' => $payload['full_name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'password' => Hash::make($payload['password']),
            'loyalty_tier' => 'standard',
            'reward_points' => 0,
        ]);

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
