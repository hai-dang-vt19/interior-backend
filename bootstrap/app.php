<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CustomerAuthenticateMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'role' => RoleMiddleware::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'customer.auth' => CustomerAuthenticateMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            if ($e->getStatusCode() == 404) {
                if (! config('app.use_domain_routing')) {
                    return $request->is('admin', 'admin/*')
                        ? redirect()->route('admin.dashboard')
                        : redirect()->route('site.home');
                }
                $host = $request->getHost();
                if ($host === config('app.admin_domain_host')) {
                    return redirect()->route('admin.dashboard');
                }
                if ($host === config('app.customer_domain_host')) {
                    return redirect()->route('site.home');
                }
            }
        });
    })->create();
