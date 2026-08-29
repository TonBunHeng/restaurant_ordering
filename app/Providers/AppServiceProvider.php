<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination.custom');

        $timezone = config('app.timezone', 'Asia/Phnom_Penh');
        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);

        View::share('restaurant', config('restaurant'));
    }
}
