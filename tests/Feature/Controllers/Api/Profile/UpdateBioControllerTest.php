<?php

namespace Tests\Feature\Controllers\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class UpdateBioControllerTest extends TestCase
{
    use RefreshDatabase, JsonRequestForValidation;

    public function test_updateName_method()
    {
        $user = User::factory()->user()->create();
        $newBio = 'This is new bio for me.';

        $this->actingAs($user);

        $this->postJson(route('api.update.bio'), ['bio' => $newBio])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Update bio to {$newBio}.",
                'success' => true,
            ]);

        $user->refresh();

        $this->assertEquals($newBio, $user->bio);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    public function test_updateBio_validation_required_data()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [];
        $errors = [
            'bio' => 'The bio field is required.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.bio'), $data, $errors);
    }

    public function test_updateBio_validation_name_has_string_rule()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'bio' => 12,
        ];
        $errors = [
            'bio' => 'The bio must be a string.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.bio'), $data, $errors);
    }

    public function test_updateName_validation_name_has_max_1023_rule()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'bio' => Str::random(1024),
        ];
        $errors = [
            'bio'     => 'The bio must not be greater than 1023 characters.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.bio'), $data, $errors);
    }

}