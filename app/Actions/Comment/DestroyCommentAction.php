<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/22/2022
 * Time: 2:41 PM
 */

namespace App\Actions\Comment;


use App\Events\Comment\DestroyedCommentEvent;
use App\Models\Comment;

class DestroyCommentAction
{

    public function execute(Comment $commentToDelete)
    {
        //todo check authorization
        $commentId = $commentToDelete->id;
        $commentToDelete->likes()->delete();
        $commentToDelete->delete();
        event(new DestroyedCommentEvent($commentId));
    }
}