<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/22/2022
 * Time: 9:37 PM
 */

namespace App\Actions\Comment;


use App\Events\Comment\LikedCommentEvent;
use App\Exceptions\LikeException;
use App\Models\Comment;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Response;

class LikeCommentAction
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
        if ($comment->likes()->where('user_id', $user->id)->first()) {
            throw new LikeException(
                LikeException::USER_ALREADY_LIKED_COMMENT,
                LikeException::USER_ALREADY_LIKED_COMMENT_STATUS_CODE);
        }
        $comment->likes()->create(['user_id' => $user->id]);

        event(new LikedCommentEvent($user, $comment));
    }
}