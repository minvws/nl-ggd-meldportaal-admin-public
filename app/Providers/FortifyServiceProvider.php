<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Responses\FailedTwoFactorLoginResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });

        $this->app->singleton(
            FailedTwoFactorLoginResponseContract::class,
            FailedTwoFactorLoginResponse::class
        );

        // Login with lowercase email
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', strtolower($request->email))->first();
            if (!$user || !$request->password || !$user->password) {
                return false;
            }
            if (Hash::check($request->password, $user->password)) {
                return $user;
            }
        });

        Fortify::twoFactorChallengeView(function () {
            return view('auth.two-factor-challenge');
        });
    }
}
