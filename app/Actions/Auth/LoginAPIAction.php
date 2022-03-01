<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/18/2022
 * Time: 7:52 PM
 */

namespace App\Actions\Auth;


use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginAPIAction
{

    public function execute(array $data)
    {
        if (!Auth::attempt($data)) {
            throw ValidationException::withMessages([
                //todo change error
                'email' => __('auth.failed'),
            ]);
        }

        $user = \auth()->user();

        $user->tokens()->delete();

        $token = $user->createToken('authtoken')->plainTextToken;

        return $token;
    }
}