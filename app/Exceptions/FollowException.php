<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class FollowException extends Exception
{
    const FOLLOWED_USER_BEFORE = 'You followed this user before';
    const FOLLOWED_USER_BEFORE_STATUS_CODE = Response::HTTP_UNPROCESSABLE_ENTITY;

    const DIDNT_FOLLOWED_USER_BEFORE = 'You didnt followed this user before';
    const DIDNT_FOLLOWED_USER_BEFORE_STATUS_CODE = Response::HTTP_UNPROCESSABLE_ENTITY;

    const DIDNT_HAVE_FOLLOW_REQUEST_FROM_THIS_USER_TO_ACCEPT = 'You didnt have any follow request from this user to accept';
    const DIDNT_HAVE_FOLLOW_REQUEST_FROM_THIS_USER_TO_ACCEPT_STATUS_CODE = Response::HTTP_UNPROCESSABLE_ENTITY;

    const DIDNT_HAVE_FOLLOW_REQUEST_FROM_THIS_USER_TO_REJECT = 'You didnt have any follow request from this user to reject';
    const DIDNT_HAVE_FOLLOW_REQUEST_FROM_THIS_USER_TO_REJECT_STATUS_CODE = Response::HTTP_UNPROCESSABLE_ENTITY;
}
