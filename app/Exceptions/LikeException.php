<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class LikeException extends Exception
{
    const USER_ALREADY_LIKED_POST = 'You have already liked this post.';
    const USER_ALREADY_LIKED_POST_STATUS_CODE = Response::HTTP_UNPROCESSABLE_ENTITY;

    const USER_DIDNT_LIKE_POST_BEFORE = 'You didnt liked this post before.';
    const USER_DIDNT_LIKE_POST_BEFORE_STATUS_CODE = Response::HTTP_UNPROCESSABLE_ENTITY;

    const USER_ALREADY_LIKED_COMMENT = 'You have already liked this comment.';
    const USER_ALREADY_LIKED_COMMENT_STATUS_CODE = Response::HTTP_UNPROCESSABLE_ENTITY;

    const USER_DIDNT_LIKE_COMMENT_BEFORE = 'You didnt liked this comment before.';
    const USER_DIDNT_LIKE_COMMENT_BEFORE_STATUS_CODE = Response::HTTP_UNPROCESSABLE_ENTITY;
}