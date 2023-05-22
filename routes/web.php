<?php

// phpcs:ignoreFile

declare(strict_types=1);

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApiUserController;
use App\Http\Controllers\MeldportaalPdfController;
use App\Http\Controllers\MeldportaalUserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*/ Unauthenticated User Routes /*/
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Change the language of the page
Route::get('ChangeLanguage/{locale}', function ($locale) {
    if (in_array($locale, \Config::get('app.locales'))) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('changelang');

Route::middleware(['isAuthenticatedUser', 'password', 'active', 'role:super_admin,user_admin'])
    ->group(callback: function () {
        Route::get('/', function () {
            return view('home');
        })->name('home');

        generateRoutes('meldportaal', MeldportaalUserController::class, MeldportaalPdfController::class);

        Route::middleware(['role:super_admin'])->group(callback: function () {
            generateRoutes('api', ApiUserController::class, MeldportaalPdfController::class);
        });

        // View/update password
        Route::get('/account', function () {
            $user = Auth::user();
            if ($user) {
                if ($user->canChangePassword()) {
                    return view('profile.show');
                }
                if ($user->isUzi()) {
                    return view('profile.uzi');
                }
            }
            return redirect()->route('home');
        })->name('profile.show');

        Route::post('/account/update-password', [AccountController::class, 'changePassword'])
            ->name('profile.update_password');
    });

function generateRoutes(string $prefix, string $class, string $pdfClass): void
{
    Route::match(['get', 'post'], '/users/' . $prefix, [$class, 'overview'])
        ->name('users.' . $prefix . '.overview');

    Route::get('/users/' . $prefix . '/entry/{id}', [$class, 'view'])
        ->name('users.' . $prefix . '.view');

    Route::get('/users/' . $prefix . '/create', [$class, 'create'])
        ->name('users.' . $prefix . '.create');
    Route::post('/users/' . $prefix . '/create', [$class, 'store'])
        ->name('users.' . $prefix . '.store');

    Route::get('/users/' . $prefix . '/entry/{id}/edit', [$class, 'edit'])
        ->name('users.' . $prefix . '.edit');

    Route::post('/users/' . $prefix . '/entry/{id}/reset', [$class, 'reset'])
        ->name('users.' . $prefix . '.reset_credentials');

    Route::post('/users/' . $prefix . '/entry/{id}/deactivate', [$class, 'deactivate'])
        ->name('users.' . $prefix . '.deactivate');
    Route::post('/users/' . $prefix . '/entry/{id}/activate', [$class, 'activate'])
        ->name('users.' . $prefix . '.activate');

    Route::post('/users/' . $prefix . '/entry/{id}/roles', [$class, 'roles'])
        ->name('users.' . $prefix . '.roles');
    Route::post('/users/' . $prefix . '/entry/{id}/account', [$class, 'account'])
        ->name('users.' . $prefix . '.account');

    Route::get('/users/' . $prefix . '/pdf', [$pdfClass, 'overview'])
        ->name('pdf.' . $prefix . '.overview');
    Route::get('/users/' . $prefix . '/pdf/{uuid}/download', [$pdfClass, 'download'])
        ->name('pdf.' . $prefix . '.download');
}
