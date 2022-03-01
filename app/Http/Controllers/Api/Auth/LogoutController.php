<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LogoutAPIAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LogoutController extends Controller
{
    use APIResponseHelper;

    public function logout(LogoutAPIAction $logoutAPIAction)
    {
        $logoutAPIAction->execute();

        return
            $this->send_custom_response(null,
                'Logged out successfully',
                Response::HTTP_OK,
                true);
    }
}
