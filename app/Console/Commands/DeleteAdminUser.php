<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:delete {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes admin user based on email';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
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

        $result = $user->delete();
        if (!$result) {
            $this->error('Could not delete user');
        }

        $this->info('Successfully deleted user');
        return 0;
    }
}
