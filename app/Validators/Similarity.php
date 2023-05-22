<?php

declare(strict_types=1);

namespace App\Validators;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * Checks if a password is not similar to either the email or username.
 *
 * @package App\Rules
 * @author jthijssen@noxlogic.nl
 */
class Similarity
{
    public const THRESHOLD = 5;

    /**
     * @param mixed $field
     * @param mixed $value
     * @param mixed $param
     * @param Validator $validator
     * @return bool
     */
    public function check($field, $value, $param, Validator $validator): bool
    {
        $user = Auth::User();
        if (is_null($user)) {
            return true;
        }

        if (levenshtein($user->email, $value) <= self::THRESHOLD) {
            $msg = __('Password cannot be similar to your email address.');
            if (! is_string($msg)) {
                $msg = 'Password cannot be similar to your email address.';
            }

            $validator->errors()->add($field, $msg);
            return false;
        }

        if (levenshtein($user->name, $value) <= self::THRESHOLD) {
            $msg = __('Password cannot be similar to your name.');
            if (! is_string($msg)) {
                $msg = 'Password cannot be similar to your name.';
            }

            $validator->errors()->add($field, $msg);
            return false;
        }

        return true;
    }
}
