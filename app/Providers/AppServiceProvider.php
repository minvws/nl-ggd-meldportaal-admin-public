<?php

declare(strict_types=1);

namespace App\Providers;

use Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        Validator::extend('commonlist', 'App\Validators\CommonList@check');
        Validator::extend('not_numeric', 'App\Validators\NotNumeric@check');
        Validator::extend('similarity', 'App\Validators\Similarity@check');
        Validator::extend('supporting_roles', 'App\Validators\SupportingRoles@check');

        Paginator::defaultView('pagination::rijkshuisstijl');
        Paginator::defaultSimpleView('pagination::rijkshuisstijl');

        $this->addBladeFunctions();
    }


    private function addBladeFunctions(): void
    {
        Blade::if('role', function ($roles) {
            $user = Auth::user();
            if (is_null($user)) {
                return false;
            }

            if (!is_array($roles)) {
                $roles = [$roles];
            }
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }

            return false;
        });
    }
}
