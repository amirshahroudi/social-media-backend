<?php

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Profile\UpdateProfileImageAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\UpdateProfileImageRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UpdateProfileImageController extends Controller
{
    use APIResponseHelper;

    public function updateProfileImage(UpdateProfileImageRequest $request, UpdateProfileImageAction $action)
    {
        $image = $request->file('profile_image');
        $action->execute($image);

        return
            $this->send_custom_response(
                null,
                'Update profile image.',
                Response::HTTP_OK,
                true);
    }
}
