<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\UserException;
use App\Role;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;

/**
 * Bridge service to generate users from HTTP request data
 * @package App\Services
 */
class UserRequestService
{
    protected UserGeneratorService $userGeneratorService;

    public function __construct(UserGeneratorService $userGeneratorService)
    {
        $this->userGeneratorService = $userGeneratorService;
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     * @throws UserException
     * @throws \Throwable
     */
    public function createNewUserFromRequest(string $userclass, Request $request): array
    {
        $input = $request->all();
        $roles = Role::parseFromRequest($request->get('roles', []));

        list($user, $password) = $this->userGeneratorService->createNewUser(
            $userclass,
            $input['email'] ?? '',
            $input['name'],
            $roles,
            $input['serial'] ?? '',
        );

        return [ $user, $password ];
    }
}
