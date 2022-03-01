<?php

namespace App\Http\Controllers\Api\Comment;

use App\Actions\Comment\DestroyCommentAction;
use App\Actions\Comment\LikeCommentAction;
use App\Actions\Comment\UnlikeCommentAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    use RefreshDatabase, APIResponseHelper;

    public function destroy(Comment $comment, DestroyCommentAction $action)
    {
        $this->authorize('destroy', $comment);

        $action->execute($comment);

        return
            $this->send_custom_response(
                null,
                'Comment destroyed successfully.',
                Response::HTTP_OK,
                true
            );
    }

    public function like(Comment $comment, LikeCommentAction $action)
    {
        $this->authorize('like', $comment);

        $action->execute($comment);

        return
            $this->send_custom_response(
                null,
                'Comment liked successfully.',
                Response::HTTP_OK,
                true
            );
    }

    public function unlike(Comment $comment, UnlikeCommentAction $action)
    {
        $this->authorize('like', $comment);

        $action->execute($comment);

        return
            $this->send_custom_response(
                null,
                'Comment unliked successfully.',
                Response::HTTP_OK,
                true
            );
    }
}
