<?php

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Profile\AcceptFollowRequestAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AcceptFollowRequestController extends Controller
{
    use APIResponseHelper;

    public function accept(User $user, AcceptFollowRequestAction $acceptFollowRequestAction)
    {
        $acceptFollowRequestAction->execute($user);

        return
            $this->send_custom_response(
                null,
                "Accepted follow request from {$user->username}.",
                Response::HTTP_OK,
                true
            );
    }
}
