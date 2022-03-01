<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/22/2022
 * Time: 1:00 PM
 */

namespace App\Actions\Post;


use App\Events\Post\UnlikedPostEvent;
use App\Exceptions\LikeException;
use App\Models\Post;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Response;

class UnlikePostAction
{

    /**
     * @param Post $post
     * @throws LikeException
     * @throws AuthenticationException
     */
    public function execute(Post $post)
    {
        if (!auth()->check()) {
            throw new AuthenticationException();
        }

        $user = auth()->user();
        if (!$like = $post->likes()->where('user_id', $user->id)->first()) {
            throw new LikeException(
                LikeException::USER_DIDNT_LIKE_POST_BEFORE,
                LikeException::USER_DIDNT_LIKE_POST_BEFORE_STATUS_CODE);
        }
        $like->delete();

        event(new UnlikedPostEvent($user,$post));
    }
}