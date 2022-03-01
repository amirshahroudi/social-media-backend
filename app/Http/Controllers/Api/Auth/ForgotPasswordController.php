<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\ForgotPasswordAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ForgotPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    use APIResponseHelper;

    public function forgotPassword(ForgotPasswordRequest $request, ForgotPasswordAction $forgotPasswordAction)
    {
        $email = $request->validated()['email'];

        $status = $forgotPasswordAction->execute($email);

        return $status == Password::RESET_LINK_SENT
            ? $this->send_custom_response(null,
                "Reset password emailed to {$email}",
                Response::HTTP_OK,
                true)
            : $this->send_custom_response(null,
                "We can't find a user with that email address.",
                Response::HTTP_UNPROCESSABLE_ENTITY,
                false);
    }
}
