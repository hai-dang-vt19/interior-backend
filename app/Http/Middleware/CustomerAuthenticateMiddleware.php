<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuthenticateMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->guard('customer')->check()) {
            return redirect()->route('site.login');
        }

        return $next($request);
    }
}
