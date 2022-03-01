<?php

namespace Tests\Feature\Controllers\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class UpdatePrivacyControllerTest extends TestCase
{
    use RefreshDatabase, JsonRequestForValidation;

    public function test_updatePrivacy_method_change_to_private()
    {
        $user = User::factory()->user()->publicAccount()->create();

        $privacy = 'private';

        $this->actingAs($user);

        $this->postJson(route('api.update.privacy'), ['privacy' => $privacy])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Update privacy to {$privacy}.",
                'success' => true,
            ]);

        $user->refresh();

        $this->assertEquals(User::PRIVATE_ACCOUNT, $user->privacy);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    public function test_updatePrivacy_method_change_to_public()
    {
        $user = User::factory()->user()->privateAccount()->create();

        $privacy = 'public';

        $this->actingAs($user);

        $this->postJson(route('api.update.privacy'), ['privacy' => $privacy])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Update privacy to {$privacy}.",
                'success' => true,
            ]);

        $user->refresh();

        $this->assertEquals(User::PUBLIC_ACCOUNT, $user->privacy);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    public function test_updatePrivacy_validation_required_data()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'privacy' => 1213,
        ];
        $errors = [
            'privacy' => 'The selected privacy is invalid.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.privacy'), $data, $errors);
    }

}
