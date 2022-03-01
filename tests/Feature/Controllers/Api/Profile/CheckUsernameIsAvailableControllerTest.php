<?php

namespace Tests\Feature\Controllers\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class CheckUsernameIsAvailableControllerTest extends TestCase
{
    use RefreshDatabase, JsonRequestForValidation;

    public function test_isAvailable_method()
    {
        $username = 'amir';

        $this->postJson(route('api.isUsernameAvailable'), ['username' => $username])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "{$username} is available.",
                'success' => true,
            ]);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api']);
    }

    public function test_isAvailable_method_when_username_is_not_available()
    {
        $username = 'amir';
        User::factory()->state(['username' => $username])->create();

        $this->postJson(route('api.isUsernameAvailable'), ['username' => $username])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => "{$username} has taken.",
                'success' => false,
            ]);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api']);
    }

    public function test_isAvailable_validation_required_data()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [];
        $errors = [
            'username' => 'The username field is required.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.isUsernameAvailable'), $data, $errors);
    }

    public function test_isAvailable_validation_username_has_string_rule()
    {
        $data = [
            'username' => 12,
        ];
        $errors = [
            'username' => 'The username must be a string.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.isUsernameAvailable'), $data, $errors);
    }

    public function test_isAvailable_validation_username_has_max_255_rule()
    {
        $data = [
            'username' => Str::random(256),
        ];
        $errors = [
            'username' => 'The username must not be greater than 255 characters.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.isUsernameAvailable'), $data, $errors);
    }
}