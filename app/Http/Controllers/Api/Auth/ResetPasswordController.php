<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    use RefreshDatabase, APIResponseHelper;

    public function resetPassword(ResetPasswordRequest $request, ResetPasswordAction $resetPasswordAction)
    {
        $validated = $request->validated();

        $status = $resetPasswordAction->execute(
            $validated['email'], $validated['password'],
            $validated['password'], $validated['token']
        //password_confirmation validate in request
        );

        return $status == Password::PASSWORD_RESET
            ? $this->send_custom_response(null,
                "Password for {$validated['email']} updated",
                Response::HTTP_OK,
                true)
            : $this->send_custom_response(null,
                'Password reset token is invalid.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                false);
    }
}