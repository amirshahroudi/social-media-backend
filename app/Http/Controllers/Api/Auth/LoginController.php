<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginAPIAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use Illuminate\Http\Response;

class LoginController extends Controller
{
    use APIResponseHelper;

    public function login(LoginRequest $request, LoginAPIAction $loginAPIAction)
    {
        $token = $loginAPIAction->execute($request->only('email', 'password'));

        return
            $this->send_custom_response(
                ['token' => $token],
                null,
                Response::HTTP_OK,
                true
            );
    }
}