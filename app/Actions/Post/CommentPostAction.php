<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/21/2022
 * Time: 10:53 PM
 */

namespace App\Actions\Post;


use App\Events\Post\CommentedPostEvent;
use App\Models\Post;
use Illuminate\Auth\AuthenticationException;

class CommentPostAction
{

    /**
     * @param Post $post
     * @param $commentText
     * @throws AuthenticationException
     */
    public function execute(Post $post, $commentText)
    {
        if (!auth()->check()) {
            throw new AuthenticationException();
        }
        $user = auth()->user();
        $post->comments()->create([
            'text'    => $commentText,
            'user_id' => $user->id,
        ]);
        event(new CommentedPostEvent($user, $post));
    }
}