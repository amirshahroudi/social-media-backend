<?php

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Profile\UnfollowUserAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnfollowUserController extends Controller
{
    use APIResponseHelper;

    public function unfollow(User $user, UnfollowUserAction $unfollowUserAction)
    {
        $unfollowUserAction->execute($user);

        return
            $this->send_custom_response(
                null,
                "Unfollowed {$user->username}.",
                Response::HTTP_OK,
                true
            );
    }
}
