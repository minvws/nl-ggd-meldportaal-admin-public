<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Google2FA;
use Illuminate\Console\Command;

class UpdateAdminUser2FA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:2fa {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update 2fa of admin user by email';

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
        $email = $this->argument('email');
        if (!is_string($email)) {
            $this->error("Please enter a email address");
            return 1;
        }

        $user = User::whereEmail($email)->first();
        if (!$user) {
            $this->error("Could not find user with email " . $email);
            return 1;
        }

        $user->forceFill([
            'two_factor_secret' => encrypt($this->google2fa->generateSecretKey()),
            'two_factor_recovery_codes' => null,
        ]);
        $user->save();

        $this->info($user->twoFactorQrCodeUrl());

        return 0;
    }
}
