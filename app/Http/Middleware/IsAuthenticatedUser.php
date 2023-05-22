<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Role as BaseRole;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use MinVWS\PUZI\Exceptions\UziAllowedRoleException;
use MinVWS\PUZI\Exceptions\UziAllowedTypeException;
use MinVWS\PUZI\Exceptions\UziCaException;
use MinVWS\PUZI\Exceptions\UziCardExpired;
use MinVWS\PUZI\Exceptions\UziCertificateNotUziException;
use MinVWS\PUZI\UziReader;
use MinVWS\PUZI\UziValidator;
use Symfony\Component\HttpFoundation\Request;

class IsAuthenticatedUser
{
    protected UziReader $reader;
    protected UziValidator $validator;

    public function __construct(UziReader $reader, UziValidator $validator)
    {
        $this->reader = $reader;
        $this->validator = $validator;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Check if a user is currently logged in through the session
        if (Auth::check()) {
            return $next($request);
        }

        if (env('FEATURE_AUTH_UZI') === false) {
            // Seems we are logged in with an UZI account, but UZI authentication is disabled.
            if (Auth::user() && Auth::user()->isUzi()) {
                Auth::logout();
            }
            return redirect(route('login'));
        }

        return $this->handleUzi($request, $next);
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    protected function handleUzi($request, Closure $next)
    {
        // No UZI certificate found
        if (
            !$request->server->has('SSL_CLIENT_VERIFY') ||
            $request->server->get('SSL_CLIENT_VERIFY') !== 'SUCCESS'
        ) {
            // Nothing found, redirect to login
            return redirect(route('login'));
        }

        // Check UZI certificate
        try {
            // Validate the certificate from the request
            $this->validator->validate($request);

            // Fetch uziINfo
            $uziInfo = $this->reader->getDataFromRequest($request);
            if (!$uziInfo) {
                abort(403, 'Uw UZI pas dient een Zorgverlenerpas te zijn.');
            }

            $uziUserName = $uziInfo->getGivenName() . ' ' . $uziInfo->getSurName();

            $user = Auth::user();
            if ($user && $user->uzi_number === $uziInfo->getUziNumber()) {
                // User is already logged in with this UZI number
                return $next($request);
            }

            /** @var User|null $user */
            $user = User::whereUziNumber($uziInfo->getUziNumber())->first();
            if (!$user) {
                // Create authenticated user with UZI info
                $user = new User();
                $user->name = $uziUserName;
                $user->email = $uziInfo->getUziNumber() . '@uzi.pas';
                $user->password = Hash::make(random_bytes(32));
                $user->password_updated_at = now();

                $user->uzi_number = $uziInfo->getUziNumber();
                $user->active = true;
                $user->roles = [BaseRole::USER_ADMIN, BaseRole::USER];

                $user->save();
            } elseif ($user->name !== $uziUserName) {
                $user->name = $uziUserName;
                $user->save();
            }

            Auth::login($user);

            return $next($request);
        } catch (UziCaException $e) {
            abort(403, "Uw UZI pas dient een Zorgverlenerpas te zijn, Uw pas voldoet hier niet aan.");
        } catch (UziAllowedRoleException $e) {
            abort(403, "Uw UZI-pas bevat géén van de toegestane rollen.");
        } catch (UziCardExpired $e) {
            abort(403, "Uw UZI-pas is verlopen.");
        } catch (UziCertificateNotUziException $e) {
            abort(403, "Geen geldige UZI pas.");
        } catch (UziAllowedTypeException $e) {
            abort(403, "Uw UZI pastype is ongeldig voor gebruik binnen het meldportaal.");
        } catch (\Throwable $e) {
            abort(403, "Een onbekende UZI fout is opgetreden");
        }
    }
}
