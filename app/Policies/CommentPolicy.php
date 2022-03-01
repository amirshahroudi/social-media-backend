<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization, isAdmin;

    /*
     * can destroy if
     *      admin
     *      comment owner
     *      post owner
     */
    public function destroy(User $user, Comment $comment)
    {
        return
            $this->isAdmin($user)
            ||
            $this->isCommentOwner($user, $comment)
            ||
            $this->isPostOwner($user, $comment);
    }

    public function like(User $user, Comment $comment)
    {
        return
            $this->isPostOwner($user, $comment)
            ||
            $this->isPublicAccount($comment)
            ||
            $this->isPostOwnerFollowed($user, $comment);
    }

    private function isCommentOwner(User $user, Comment $comment)
    {
        return $comment->user->id === $user->id;
    }

    private function isPostOwner(User $user, Comment $comment)
    {
        return $comment->post->user->id === $user->id;
    }

    private function isPostOwnerFollowed(User $user, Comment $comment)
    {
        return $comment->post()->first()->user->followers()->where('follower_id', $user->id)->first();
    }

    private function isPublicAccount(Comment $comment)
    {
        return $comment->post()->first()->user->privacy == User::PUBLIC_ACCOUNT;
    }
}
