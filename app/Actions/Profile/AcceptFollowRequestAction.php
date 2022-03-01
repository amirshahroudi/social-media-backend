<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/23/2022
 * Time: 9:56 PM
 */

namespace App\Actions\Profile;


use App\Events\Profile\AcceptedFollowRequestEvent;
use App\Exceptions\FollowException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;

class AcceptFollowRequestAction
{

    /**
     * @param User $requester
     * @throws AuthenticationException
     * @throws FollowException
     */
    public function execute(User $requester)
    {
        //todo if user already in followers -- it cannot occur but check it again
        if (!auth()->check()) {
            throw new AuthenticationException();
        }
        $me = auth()->user();
        if (!$requester->sentFollowRequests()->where('request_to', $me->id)->first()) {
            throw new FollowException(FollowException::DIDNT_HAVE_FOLLOW_REQUEST_FROM_THIS_USER_TO_ACCEPT,
                FollowException::DIDNT_HAVE_FOLLOW_REQUEST_FROM_THIS_USER_TO_ACCEPT_STATUS_CODE);
        }
        $requester->sentFollowRequests()->detach($me);
        $requester->followings()->attach($me);
        event(new AcceptedFollowRequestEvent($me, $requester));
    }
}