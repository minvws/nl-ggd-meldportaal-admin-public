<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array
     */
    protected function passwordRules()
    {
        return [
            'required',     // A password is required
            'string',       // A password must be a string
            'min:14',       // A password must have at least 14 characters
            'commonlist',   // A password must not be present on a common list of passwords
            'not_numeric',  // A password may not be completely numeric
            'similarity',   // A password may not be similar to your name or email address
            'confirmed'     // A password must be confirmed (within the password_confirmation field)
        ];
    }
}
