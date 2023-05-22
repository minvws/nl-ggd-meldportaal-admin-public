<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * @param array $attributes
     * @param bool $own
     *
     * @return User
     */
    protected function setupUser(array $attributes = [], bool $own = false): User
    {
        /** @var User $user */
        $user = User::factory()->create($attributes);
        $user->password_updated_at = now();
        $user->active = true;
        if ($own) {
            $this->be($user);
        }
        return $user;
    }
}
