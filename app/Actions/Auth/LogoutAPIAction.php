<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/18/2022
 * Time: 9:54 PM
 */

namespace App\Actions\Auth;


use Illuminate\Auth\AuthenticationException;

class LogoutAPIAction
{

    /**
     * @throws AuthenticationException
     */
    public function execute()
    {
        if (!auth()->check()) {
            throw new AuthenticationException('You have not been logged in.');
        }
        \auth()->user()->tokens()->delete();
    }
}