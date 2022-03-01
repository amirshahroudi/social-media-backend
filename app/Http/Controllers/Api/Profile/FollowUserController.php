<?php

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Profile\FollowUserAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FollowUserController extends Controller
{
    use APIResponseHelper;

    public function follow(User $user, FollowUserAction $followUserAction)
    {
        $followUserAction->execute($user);

        return $user->privacy == User::PUBLIC_ACCOUNT
            ?
            $this->send_custom_response(
                null,
                "{$user->username} followed successfully.",
                Response::HTTP_OK,
                true
            )
            :
            $this->send_custom_response(
                null,
                "Sent follow request to {$user->username} successfully.",
                Response::HTTP_OK,
                true
            );
    }
}