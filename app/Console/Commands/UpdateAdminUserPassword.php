<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class UpdateAdminUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:pwd {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update password of admin user by email';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
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

        $password = $this->argument('password');
        if (!is_string($password)) {
            $this->error("Incorrect password");
            return 1;
        }

        $user->forceFill([
            'password' => Hash::make($password),
            'password_updated_at' => now(),
        ]);
        $user->save();

        return 0;
    }
}
