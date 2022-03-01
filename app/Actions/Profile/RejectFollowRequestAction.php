<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/24/2022
 * Time: 11:37 AM
 */

namespace App\Actions\Profile;


use App\Events\Profile\RejectedFollowRequestEvent;
use App\Exceptions\FollowException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;

class RejectFollowRequestAction
{

    /**
     * @param User $requester
     * @throws AuthenticationException
     * @throws FollowException
     */
    public function execute(User $requester)
    {
        if (!auth()->check()) {
            throw new AuthenticationException();
        }
        $me = auth()->user();
        if (!$requester->sentFollowRequests()->where('request_to', $me->id)->first()) {
            throw new FollowException(FollowException::DIDNT_HAVE_FOLLOW_REQUEST_FROM_THIS_USER_TO_REJECT,
                FollowException::DIDNT_HAVE_FOLLOW_REQUEST_FROM_THIS_USER_TO_REJECT_STATUS_CODE);
        }
        $requester->sentFollowRequests()->detach($me);
        event(new RejectedFollowRequestEvent($me, $requester));
    }
}