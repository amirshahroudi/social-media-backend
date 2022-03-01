<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization, isPublicAccount;

    private function isOwner(User $requester, User $user)
    {
        return $requester->id === $user->id;
    }

    private function isOwnerFollowed(User $requester, User $user)
    {
        return $user->followers()->where('follower_id', $requester->id)->first();
    }

    public function userInformation(User $requester, User $user)
    {
        return
            $this->isPublicAccount($user)
            ||
            $this->isOwner($requester, $user)
            ||
            $this->isOwnerFollowed($requester, $user);
    }
}
