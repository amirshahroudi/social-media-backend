<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/21/2022
 * Time: 7:39 PM
 */

namespace App\Actions\Post;


use App\Events\Post\LikedPostEvent;
use App\Exceptions\LikeException;
use App\Models\Post;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Response;

class LikePostAction
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
        if ($post->likes()->where('user_id', $user->id)->first()) {
            throw new LikeException(
                LikeException::USER_ALREADY_LIKED_POST,
                LikeException::USER_ALREADY_LIKED_POST_STATUS_CODE);
        }
        $post->likes()->create(['user_id' => $user->id]);
        event(new LikedPostEvent($user, $post));
    }
}