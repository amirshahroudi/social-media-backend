<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use Illuminate\Http\Response;

class RegisterController extends Controller
{
    use APIResponseHelper;

    public function register(RegisterRequest $request, RegisterUserAction $registerUserAction)
    {
        //todo profile image url validation
        $registerUserAction->execute(
            $request->name,
            $request->username,
            $request->bio,
            $request->profile_image_url,
            $request->email,
            $request->password
        );
        return
            $this->send_custom_response(
                null,
                "{$request['username']} created successfully",
                Response::HTTP_CREATED,
                true
            );
    }
}
