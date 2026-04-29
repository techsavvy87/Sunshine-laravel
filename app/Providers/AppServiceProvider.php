<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Service;

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
        $dompdfPublicPath = realpath(base_path('public'));

        // cPanel deployments often serve from public_html while app code lives elsewhere.
        if ($dompdfPublicPath === false && !empty($_SERVER['DOCUMENT_ROOT'])) {
            $dompdfPublicPath = realpath((string) $_SERVER['DOCUMENT_ROOT']);
        }

        if ($dompdfPublicPath !== false) {
            config(['dompdf.public_path' => $dompdfPublicPath]);
        }

        View::composer('layouts.main', function ($view) {
            $services = Service::where('status', 'active')->where('level', 'primary')->get();
            $view->with('servicesForMenu', $services);
        });
    }
}
