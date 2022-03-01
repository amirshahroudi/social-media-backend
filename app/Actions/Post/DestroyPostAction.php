<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/19/2022
 * Time: 8:47 PM
 */

namespace App\Actions\Post;


use App\Events\Post\DestroyedPostEvent;
use App\Models\Post;

class DestroyPostAction
{

    public function execute(Post $post)
    {
        //todo check authorization
        $postId = $post->id;
        $post->likes()->delete();
        $post->delete();
        event(new DestroyedPostEvent($postId));
    }
}