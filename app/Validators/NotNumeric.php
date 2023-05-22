<?php

declare(strict_types=1);

namespace App\Validators;

use Illuminate\Contracts\Validation\Validator;

/**
 * Checks if a password is not all numerical
 *
 * @package App\Rules
 * @author jthijssen@noxlogic.nl
 */
class NotNumeric
{
    /**
     * @param mixed $field
     * @param mixed $value
     * @param mixed $param
     * @param Validator $validator
     * @return bool
     */
    public function check($field, $value, $param, Validator $validator): bool
    {
        if (preg_match('/^[0-9]+$/', $value)) {
            $msg = __('Password cannot be all numerical.');
            if (! is_string($msg)) {
                $msg = 'Password cannot be all numerical.';
            }

            $validator->errors()->add($field, $msg);
            return false;
        }

        return true;
    }
}
