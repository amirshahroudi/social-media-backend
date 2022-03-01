<?php

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Profile\UpdateNameAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\UpdateNameRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UpdateNameController extends Controller
{
    use APIResponseHelper;

    public function updateName(UpdateNameRequest $request, UpdateNameAction $updateNameAction)
    {
        $newName = $request->validated()['name'];
        $updateNameAction->execute($newName);

        return
            $this->send_custom_response(
                null,
                "Update name to {$newName}.",
                Response::HTTP_OK,
                true);
    }
}
