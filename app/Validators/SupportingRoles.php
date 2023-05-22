<?php

declare(strict_types=1);

namespace App\Validators;

use Illuminate\Contracts\Validation\Validator;

/**
 * Checks if the list of roles contains at least 1 non-supporting role
 *
 * @package App\Rules
 * @author jthijssen@noxlogic.nl
 */
class SupportingRoles
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
        if (! is_array($param)) {
            $param = [ $param ];
        }
        if (! is_array($value)) {
            $value = [ $value ];
        }

        $res = array_diff($value, $param);

        return !empty($res);
    }
}
