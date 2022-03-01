<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResourceCollection;
use App\Http\Resources\UserResourceCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use APIResponseHelper;

    public function getUserInfo(User $user)
    {
        return
            $this->send_custom_response(
                [
                    'bio'             => $user->bio,
                    'post_count'      => $user->post_count,
                    'follower_count'  => $user->follower_count,
                    'following_count' => $user->following_count,
                ],
                null,
                Response::HTTP_OK,
                true);
    }

    public function getFollowers(User $user)
    {
        $this->authorize('userInformation', $user);

        return new UserResourceCollection($user->followers()->latest('user_user.created_at')->paginate(10));
    }

    public function getFollowings(User $user)
    {
        $this->authorize('userInformation', $user);

        return new UserResourceCollection($user->followings()->latest('user_user.created_at')->paginate(10));
    }

    public function getPosts(User $user)
    {
        $this->authorize('userInformation', $user);

        return new PostResourceCollection($user->posts()->latest()->paginate(10));
    }

    public function getProfileImage(User $user)
    {
        if (!is_null($user->profile_image_url)) {
            return Storage::disk('public')->get($user->profile_image_url);
        }
        return null;
    }
}
