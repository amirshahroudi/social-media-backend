<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/19/2022
 * Time: 12:00 PM
 */

namespace App\Actions\Auth;


use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordAction
{

    /**
     * @param $email
     * @param $password
     * @param $password_confirmation
     * @param $token
     * @return bool
     */
    public function execute($email, $password, $password_confirmation, $token)
    {
        $status = Password::reset(
            compact('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET;
    }
}