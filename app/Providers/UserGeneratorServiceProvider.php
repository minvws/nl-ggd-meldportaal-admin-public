<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Google2FA;
use App\Services\UserGeneratorService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Minvws\HorseBattery\HorseBattery;
use MinVWS\Logging\Laravel\LogService;

class UserGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Nothing to register
    }

    public function boot(): void
    {

        $this->app->singleton(UserGeneratorService::class, function () {
            return new UserGeneratorService(
                App::make(HorseBattery::class),
                App::make(Google2FA::class),
                App::make(LogService::class)
            );
        });
    }
}
