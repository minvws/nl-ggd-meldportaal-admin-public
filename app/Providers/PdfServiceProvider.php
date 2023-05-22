<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\PdfService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class PdfServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(PdfService::class, function (Application $app) {
            return new PdfService(
                appUrl: config('app.meldportaal_url'),
            );
        });
    }
}
