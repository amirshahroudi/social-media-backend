<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    use HandlesAuthorization, isAdmin;

    //todo decide that admin can do all thing or not
//    public function before(User $user)
//    {
//        if ($user->type === User::ADMIN) {
//            return true;
//        }
//    }

    private function isPostOwner(User $user, Post $post)
    {
        return $post->user->id === $user->id;
    }

    private function isPublicAccount(Post $post)
    {
        //todo replace with trait
        return $post->user->privacy == User::PUBLIC_ACCOUNT;
    }

    private function isPostOwnerFollowed(User $user, Post $post)
    {
        return $post->user->followers()->where('follower_id', $user->id)->first();
    }

    public function show(User $user, Post $post)
    {
        return
            $this->isAdmin($user)
            ||
            $this->isPostOwner($user, $post)
            ||
            $this->isPublicAccount($post)
            ||
            $this->isPostOwnerFollowed($user, $post);
    }

    public function update(User $user, Post $post)
    {
        return $this->isPostOwner($user, $post);
    }

    public function destroy(User $user, Post $post)
    {
        return
            $this->isAdmin($user)
            ||
            $this->isPostOwner($user, $post);
    }

    public function like(User $user, Post $post)
    {
        return
            $this->isPublicAccount($post)
            ||
            $this->isPostOwner($user, $post)
            ||
            $this->isPostOwnerFollowed($user, $post);
    }
}
