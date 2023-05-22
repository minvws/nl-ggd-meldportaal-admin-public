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
 * Class PasswordChanged
 *
 * Middleware that will check if the user has updated their password
 *
 * @package App\Http\Middleware
 * @author annejan@noprotocol.nl
 */
class CheckPasswordChanged
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
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            if ($user->password_updated_at !== null) {
                return $next($request);
            }
        }

        /** @var User|null $user */
        $user = Auth::user();

        Log::alert('User without self chosen password attempting usage', [
            'user_id' => $user === null ? null : $user->id,
            'ip_address' => request()->ip()
        ]);

        abort(Response::HTTP_FORBIDDEN, 'Access denied');
    }
}
