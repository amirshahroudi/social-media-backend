<?php

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Profile\UpdatePrivacyAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UpdatePrivacyController extends Controller
{
    use APIResponseHelper;

    public function updatePrivacy(Request $request, UpdatePrivacyAction $action)
    {
        $validated = $request->validate([
            'privacy' => ['required', Rule::in(['private', 'public'])],
        ]);

        $action->execute($privacy = $validated['privacy']);
        return
            $this->send_custom_response(
                null,
                "Update privacy to {$privacy}.",
                Response::HTTP_OK,
                true);
    }
}
