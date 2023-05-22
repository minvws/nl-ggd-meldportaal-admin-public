<?php

declare(strict_types=1);

namespace App\Validators;

use Illuminate\Contracts\Validation\Validator;

/**
 * Checks a password against a list of commonly used passwords
 *
 * @author jthijssen@noxlogic.nl
 */
class CommonList
{
    protected array $commons = [];

    /**
     * CommonList constructor.
     */
    public function __construct()
    {
        $f = file(base_path('resources/upgraded-common-passwords.txt'), FILE_IGNORE_NEW_LINES);
        $this->commons =  $f !== false ? $f : [];
    }

    /**
     * @param mixed $field
     * @param mixed $value
     * @param mixed $param
     * @param Validator $validator
     * @return bool
     */
    public function check($field, $value, $param, Validator $validator): bool
    {
        if (in_array($value, $this->commons)) {
            $msg = __('Password cannot be a common password.');
            if (! is_string($msg)) {
                $msg = 'Password cannot be a common password.';
            }

            $validator->errors()->add($field, $msg);
            return false;
        }

        return true;
    }
}
