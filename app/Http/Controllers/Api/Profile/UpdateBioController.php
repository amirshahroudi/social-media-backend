<?php

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Profile\UpdateBioAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\UpdateBioRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UpdateBioController extends Controller
{
    use APIResponseHelper;

    public function updateBio(UpdateBioRequest $request, UpdateBioAction $updateBioAction)
    {
        $newBio = $request->validated()['bio'];
        $updateBioAction->execute($newBio);

        return
            $this->send_custom_response(
                null,
                "Update bio to {$newBio}.",
                Response::HTTP_OK,
                true
            );
    }
}
