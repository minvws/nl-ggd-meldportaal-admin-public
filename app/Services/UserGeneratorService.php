<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\AbstractUser;
use App\Models\User;
use App\Models\UserCredential;
use App\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Minvws\HorseBattery\HorseBattery;
use MinVWS\Logging\Laravel\Events\Logging\ResetCredentialsLogEvent;
use MinVWS\Logging\Laravel\Events\Logging\UserCreatedLogEvent;
use MinVWS\Logging\Laravel\LogService;
use Ramsey\Uuid\Uuid;

/**
 * Class UserGeneratorService
 * @package App\Services
 */
class UserGeneratorService
{
    protected HorseBattery $horseBatteryService;
    protected Google2FA $google2fa;
    protected LogService $logService;

    public function __construct(
        HorseBattery $horseBatteryService,
        Google2FA $google2fa,
        LogService $logService
    ) {
        $this->horseBatteryService = $horseBatteryService;
        $this->google2fa = $google2fa;
        $this->logService = $logService;
    }

    /**
     * Creates a new user with the given parameters. Password is automatically generated. Will generate PDF/CSV files
     *
     * @return array{AbstractUser, string}
     * @throws UserException
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     * @throws \Throwable
     */
    public function createNewUser(
        string $class,
        string $email,
        string $name,
        array $roles = [ Role::USER ],
        string $serial = null,
    ): array {
        /** @var $class AbstractUser */
        if ($class::whereEmail($email)->exists()) {
            throw UserException::alreadyExists();
        }

        $password = $this->horseBatteryService->generate(4);


        // We need to commit. Otherwise, we get nested transactions, and this results in savepoints instead of
        // real transactions. This makes that DB::commit() doesn't really commit, so it will rollback EVERYTHING
        // when for instance we get a script timeout.
        DB::commit();

        try {
            DB::beginTransaction();

            $user = $class::create([
                'email' => $email,
                'name' => $name,
                'password' => Hash::make($password),
                'uuid' => Uuid::uuid4(),
                'created_by' => Auth::user()?->id,
            ]);

            $user->forceFill([
                'roles' => $roles,
                'active' => true,
                'uzi_serial' => $serial,
                'two_factor_secret' => encrypt($this->google2fa->generateSecretKey()),
                'two_factor_recovery_codes' => null,
            ]);
            $user->save();

            $this->setCredentialsPassword($user, $password);

            /** @var User $loggedInUser */
            $loggedInUser = Auth::user();
            $this->logService->log((new UserCreatedLogEvent())
                ->asCreate()
                ->withActor($loggedInUser)
                ->withSource(config('app.name'))
                ->withData([
                    'user_id' => $user->id,
                    'last_active_at' => $loggedInUser->last_active_at,
                    'last_login_at' => $loggedInUser->last_login_at,
                    'is_api_user' => $user->hasRole(Role::API),
                    'table' => $user->getTable(),
                ]));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return array($user, $password);
    }

    /**
     * Reset the credentials (password and/or qr code) of a user. Will generate new PDF/CSV files if both password and
     * qr code are reset.
     */
    public function resetCredentials(
        AbstractUser $user,
        bool $resetPwd,
        bool $reset2fa
    ): ?string {
        $password = "";

        // Just in case we don't have a UUID yet
        if (isset($user->uuid) && empty($user->uuid)) {
            $user->uuid = (string)Uuid::uuid4();
        }

        if ($resetPwd) {
            $password = $this->horseBatteryService->generate(4);
            $user->forceFill([
                'password' => Hash::make($password),
                'password_updated_at' => null,
            ]);
            $user->save();

            $this->setCredentialsPassword($user, $password);
        } else {
            $this->setCredentialsPassword($user, "<niet gewijzigd>");
        }

        if ($reset2fa) {
            $user->forceFill([
                'two_factor_secret' => encrypt($this->google2fa->generateSecretKey()),
                'two_factor_recovery_codes' => null,
            ]);
            $user->save();
        }

        /** @var User $loggedInUser */
        $loggedInUser = Auth::user();
        $this->logService->log((new ResetCredentialsLogEvent())
            ->asUpdate()
            ->withActor($loggedInUser)
            ->withTarget($user)
            ->withSource(config('app.name'))
            ->withData([
                'user_id' => $user->id,
                'last_active_at' => $loggedInUser->last_active_at,
                'last_login_at' => $loggedInUser->last_login_at,
                'reset_password' => $resetPwd,
                'reset_2fa' => $reset2fa,
            ]));

        return $password;
    }

    /**
     * Set password in saved credentials, or create a new one if the old one is deleted
     */
    protected function setCredentialsPassword(AbstractUser $user, string $password): void
    {
        /** @var UserCredential|null $class */
        $class = $user->getCredentialClass();
        if ($class === null) {
            return;
        }

        // Save credentials in correct table for PDF generation at a later time
        $class::updateOrCreate(
            ['user_id' => $user->id],
            [
                'password' => $password,
                'twofa_url' => $user->two_factor_secret,
            ]
        );
    }
}
