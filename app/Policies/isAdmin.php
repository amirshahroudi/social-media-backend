<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 2/12/2022
 * Time: 7:32 PM
 */

namespace App\Policies;


use App\Models\User;

trait isAdmin
{
    public function isAdmin(User $user)
    {
        return $user->type === User::ADMIN;
    }
}