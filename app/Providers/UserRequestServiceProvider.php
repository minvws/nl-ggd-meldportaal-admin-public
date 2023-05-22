<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\UserGeneratorService;
use App\Services\UserRequestService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class UserRequestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Nothing to register
    }

    public function boot(): void
    {
        $this->app->singleton(UserRequestService::class, function () {
            return new UserRequestService(
                App::make(UserGeneratorService::class),
            );
        });
    }
}
