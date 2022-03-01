<?php

namespace Tests\Feature\Controllers\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class UpdateNameControllerTest extends TestCase
{
    use RefreshDatabase, JsonRequestForValidation;

    public function test_updateName_method()
    {
        $user = User::factory()->user()->create();

        $newName = 'amir';

        $this->actingAs($user);

        $this->postJson(route('api.update.name'), ['name' => $newName])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Update name to {$newName}.",
                'success' => true,
            ]);

        $user->refresh();

        $this->assertEquals($newName, $user->name);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    public function test_updateName_validation_required_data()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [];
        $errors = [
            'name' => 'The name field is required.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.name'), $data, $errors);
    }

    public function test_updateName_validation_name_has_string_rule()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'name' => 12,
        ];
        $errors = [
            'name' => 'The name must be a string.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.name'), $data, $errors);
    }

    public function test_updateName_validation_name_has_max_255_rule()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'name' => Str::random(256),
        ];
        $errors = [
            'name'     => 'The name must not be greater than 255 characters.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.name'), $data, $errors);
    }
}