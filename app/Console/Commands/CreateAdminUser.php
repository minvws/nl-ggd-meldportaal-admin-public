<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Role;
use App\Services\Google2FA;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:admin {email} {name} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates admin user that is allowed to login and create other users';

    /**
     * @var Google2FA
     */
    protected $google2fa;

    /**
     * Create a new command instance.
     *
     * @param Google2FA $google2fa
     */
    public function __construct(Google2FA $google2fa)
    {
        parent::__construct();

        $this->google2fa = $google2fa;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $passwd = $this->argument('password');
        if (!is_string($passwd)) {
            $this->error("Incorrect password");
            return 1;
        }

        $user = User::create([
            "email" => $this->argument('email'),
            "name" => $this->argument('name'),
            "password" => Hash::make($passwd),
            "roles" => [Role::SUPER_ADMIN],
        ]);

        $user->forceFill([
            'two_factor_secret' => encrypt($this->google2fa->generateSecretKey()),
            'two_factor_recovery_codes' => null,
            'password_updated_at' => now()
        ]);
        $user->save();

        $this->info($user->twoFactorQrCodeUrl());

        return 0;
    }
}
