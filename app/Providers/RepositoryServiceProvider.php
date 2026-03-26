<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $repositories = [
            'Customer',
            'Product',
            'Category',
            'Order',
            'Dashboard',
            'Staff',
        ];

        foreach ($repositories as $repo) {
            $this->app->bind(
                "App\\Repositories\\{$repo}\\{$repo}RepositoryInterface",
                "App\\Repositories\\{$repo}\\{$repo}Repository"
            );
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
