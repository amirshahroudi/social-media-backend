<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/18/2022
 * Time: 10:18 PM
 */

namespace App\Actions\Auth;


use Illuminate\Support\Facades\Password;

class ForgotPasswordAction
{

    public function execute($email)
    {
        $status = Password::sendResetLink(['email' => $email]);
        return $status == Password::RESET_LINK_SENT;
    }
}