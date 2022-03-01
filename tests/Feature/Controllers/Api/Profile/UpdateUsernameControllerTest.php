<?php

namespace Tests\Feature\Controllers\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class UpdateUsernameControllerTest extends TestCase
{
    use RefreshDatabase, JsonRequestForValidation;

    public function test_updateUsername_method()
    {
        $user = User::factory()->user()->create();

        $newUserName = 'amir';

        $this->actingAs($user);

        $this->postJson(route('api.update.username'), ['username' => $newUserName])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Update username to {$newUserName}.",
                'success' => true,
            ]);

        $user->refresh();

        $this->assertEquals($newUserName, $user->username);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    public function test_updateUsername_validation_required_data()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [];
        $errors = [
            'username' => 'The username field is required.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.username'), $data, $errors);
    }

    public function test_updateUsername_validation_name_has_string_rule()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'username' => 12,
        ];
        $errors = [
            'username' => 'The username must be a string.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.username'), $data, $errors);
    }

    public function test_updateUsername_validation_username_has_max_255_rule()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'username' => Str::random(256),
        ];
        $errors = [
            'username' => 'The username must not be greater than 255 characters.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.username'), $data, $errors);
    }

    public function test_updateUsername_validation_username_has_unique_rule()
    {
        $user = User::factory()->state(['username' => 'amir',])->create();
        $this->actingAs($user);

        $data = [
            'username' => 'amir',
        ];
        $errors = [
            'username' => 'The username has already been taken.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.username'), $data, $errors);
    }
}