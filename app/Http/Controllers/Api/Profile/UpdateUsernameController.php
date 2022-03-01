<?php

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Profile\UpdateUsernameAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\UpdateUsernameRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UpdateUsernameController extends Controller
{
    use APIResponseHelper;

    public function updateUsername(UpdateUsernameRequest $request, UpdateUsernameAction $updateUsernameAction)
    {
        $newUserName = $request->validated()['username'];
        $updateUsernameAction->execute($newUserName);

        return
            $this->send_custom_response(
                null,
                "Update username to {$newUserName}.",
                Response::HTTP_OK,
                true
            );
    }
}
