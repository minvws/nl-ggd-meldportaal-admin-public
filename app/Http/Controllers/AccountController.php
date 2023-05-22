<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountController extends BaseController
{
    use PasswordValidationRules;

    /**
     * @param Request $request
     *
     * @return RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function changePassword(Request $request)
    {
        $user = Auth::User();
        if (is_null($user)) {
            return redirect("/");
        }

        if ($user->isUzi()) {
            return redirect("/");
        }

        // Validate if password information is correct
        $input = $request->all();
        Validator::make($input, [
            'current_password' => ['required', 'string'],
            'password' => $this->passwordRules(),
        ])->after(function ($validator) use ($user, $input) {
            /** @var string $pwd */
            $pwd = $user->password;
            if (!Hash::check($input['current_password'], $pwd)) {
                $validator->errors()->add(
                    'current_password',
                    __('The provided password does not match your current password.')
                );
            } elseif ($input['current_password'] == $input['password']) {
                $validator->errors()->add(
                    'password',
                    __('The chosen password is identical to the current password')
                );
            } elseif ($input['password_confirmation'] != $input['password']) {
                $validator->errors()->add(
                    'password_confirmation',
                    __('The password confirmation does not match the password.')
                );
            }
        })->validate();

        // Add new password, and set the password updated_at so we can actually do something on the site
        $user->forceFill([
            'password' => Hash::make($input['password']),
            'password_updated_at' => now(),
        ])->save();

        return redirect("/");
    }
}
