<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/22/2022
 * Time: 7:33 PM
 */

namespace App\Actions\Profile;


use App\Events\Profile\UnfollowedUserEvent;
use App\Exceptions\FollowException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Response;

class UnfollowUserAction
{

    /**
     * @param User $shouldUnfollow
     * @throws AuthenticationException
     * @throws FollowException
     */
    public function execute(User $shouldUnfollow)
    {
        if (!auth()->check()) {
            throw new AuthenticationException();
        }
        $user = auth()->user();
        if (!$shouldUnfollow->followers()->where('follower_id', $user->id)->first()) {
            throw new FollowException(
                FollowException::DIDNT_FOLLOWED_USER_BEFORE,
                FollowException::DIDNT_FOLLOWED_USER_BEFORE_STATUS_CODE);
        }
        $shouldUnfollow->followers()->detach($user->id);
        event(new UnfollowedUserEvent($user, $shouldUnfollow));
    }
}