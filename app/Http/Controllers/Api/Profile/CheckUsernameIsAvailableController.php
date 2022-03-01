<?php

namespace App\Http\Controllers\Api\Profile;

use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CheckUsernameIsAvailableController extends Controller
{
    use APIResponseHelper;

    public function isAvailable(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
        ]);

        $username = $request->username;
        $is_find = !!User::where('username', $username)->first();

        return
            $is_find
                ? $this->send_custom_response(
                null,
                "{$username} has taken.",
                Response::HTTP_UNPROCESSABLE_ENTITY,
                false)
                : $this->send_custom_response(
                null,
                "{$username} is available.",
                Response::HTTP_OK,
                true);
    }
}
