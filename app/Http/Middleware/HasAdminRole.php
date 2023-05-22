<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HasAdminRole
 *
 * Middleware that will decline non-admin users from requesting pages
 *
 * @package App\Http\Middleware
 * @author jthijssen@noxlogic.nl
 */
class HasAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Continue when not logged in
        if (!Auth::check()) {
            return $next($request);
        }

        // If logged in, check if the user is an super admin or user admin
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole([\App\Role::SUPER_ADMIN, \App\Role::USER_ADMIN])) {
            return $next($request);
        }

        // Logout automatically, as the user cannot log out manually
        Auth::logout();

        Log::alert('Non-admin user attempting usage', [
            'user_id' => $user->id,
            'ip_address' => request()->ip()
        ]);

        abort(Response::HTTP_FORBIDDEN, 'Access denied');
    }
}
