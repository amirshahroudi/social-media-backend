<?php

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Profile\RejectFollowRequestAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RejectFollowRequestController extends Controller
{
    use APIResponseHelper;

    public function reject(User $user, RejectFollowRequestAction $rejectFollowRequestAction)
    {
        $rejectFollowRequestAction->execute($user);

        return
            $this->send_custom_response(
                null,
                "Rejected follow request from {$user->username}.",
                Response::HTTP_OK,
                true
            );
    }
}
