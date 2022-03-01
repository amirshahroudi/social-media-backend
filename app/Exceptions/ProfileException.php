<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class ProfileException extends Exception
{
    const USERNAME_ALREADY_HAVE_TAKEN = 'This username already have taken by another user.';
    const USERNAME_ALREADY_HAVE_TAKEN_STATUS_CODE = Response::HTTP_UNPROCESSABLE_ENTITY;
}
