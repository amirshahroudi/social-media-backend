<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/25/2022
 * Time: 2:06 PM
 */

namespace App\Actions\Comment;


use App\Events\Comment\UnlikedCommentEvent;
use App\Exceptions\LikeException;
use App\Models\Comment;
use Illuminate\Auth\AuthenticationException;

class UnlikeCommentAction
{

    /**
     * @param Comment $comment
     * @throws AuthenticationException
     * @throws LikeException
     */
    public function execute(Comment $comment)
    {
        if (!auth()->check()) {
            throw new AuthenticationException();
        }

        $user = auth()->user();
        if (!$like = $comment->likes()->where('user_id', $user->id)->first()) {
            throw new LikeException(
                LikeException::USER_DIDNT_LIKE_COMMENT_BEFORE,
                LikeException::USER_DIDNT_LIKE_COMMENT_BEFORE_STATUS_CODE
            );
        }
        $like->delete();

        event(new UnlikedCommentEvent($user, $comment));
    }
}