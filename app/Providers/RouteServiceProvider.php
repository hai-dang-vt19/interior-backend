<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (! config('app.use_domain_routing')) {
            // Một host duy nhất: / = site khách, /admin = quản trị (vd: http://localhost)
            Route::middleware('web')->group(base_path('routes/site.php'));
            Route::middleware('web')->group(base_path('routes/admin.php'));

            return;
        }

        $customerHost = config('app.customer_domain_host');
        $adminHost = config('app.admin_domain_host');

        // Site khách hàng — CUSTOMER_DOMAIN (ví dụ chungsi.user.localhost)
        Route::domain($customerHost)
            ->middleware('web')
            ->group(base_path('routes/site.php'));

        // Quản trị — ADMIN_DOMAIN (ví dụ chungsi.admin.localhost)
        Route::domain($adminHost)
            ->middleware('web')
            ->group(base_path('routes/admin.php'));
    }
}
