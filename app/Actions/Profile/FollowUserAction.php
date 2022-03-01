<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/22/2022
 * Time: 4:38 PM
 */

namespace App\Actions\Profile;


use App\Events\Profile\FollowedUserEvent;
use App\Events\Profile\RequestedToFollowUserEvent;
use App\Exceptions\FollowException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Response;

class FollowUserAction
{

    /**
     * @param User $shouldFollow
     * @throws AuthenticationException
     * @throws FollowException
     */
    public function execute(User $shouldFollow)
    {
        if (!auth()->check()) {
            throw new AuthenticationException();
        }
        $user = auth()->user();
        if ($shouldFollow->followers()->where('follower_id', $user->id)->first()) {
            throw new FollowException(
                FollowException::FOLLOWED_USER_BEFORE,
                FollowException::FOLLOWED_USER_BEFORE_STATUS_CODE);
        }
        if ($shouldFollow->privacy != User::PRIVATE_ACCOUNT) {
            $shouldFollow->followers()->attach($user);
            event(new FollowedUserEvent($user, $shouldFollow));
        } else {
            $shouldFollow->receivedFollowRequests()->attach($user);
            event(new RequestedToFollowUserEvent($user, $shouldFollow));
        }
    }
}