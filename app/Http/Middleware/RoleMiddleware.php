<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('admin.login');
        }

        $currentRole = is_object($user->role) ? $user->role->name : (string) $user->role;
        if (!in_array($currentRole, $roles, true)) {
            return redirect()->route('admin.dashboard')->with('dataError', 'Bạn không có quyền truy cập chức năng này');
        }

        return $next($request);
    }
}
