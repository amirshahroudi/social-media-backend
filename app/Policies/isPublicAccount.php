<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 2/17/2022
 * Time: 6:33 PM
 */

namespace App\Policies;


use App\Models\User;

trait isPublicAccount
{
    public function isPublicAccount(User $user)
    {
        return $user->privacy == User::PUBLIC_ACCOUNT;
    }
}