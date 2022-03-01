<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/19/2022
 * Time: 8:37 PM
 */

namespace App\Actions\Post;


use App\Models\Post;

class UpdatePostAction
{

    public function execute(Post $post, $newCaption)
    {
        $post->update([
            'caption' => $newCaption,
        ]);
    }
}