<?php

namespace Tests\Feature\Controllers\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class UpdateProfileImageControllerTest extends TestCase
{
    use RefreshDatabase, JsonRequestForValidation;

    public function test_updateProfileImage_method()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $profileImage = UploadedFile::fake()->image('profile.jpg');

        $this->postJson(route('api.update.profileImage'), ['profile_image' => $profileImage])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Update profile image.",
                'success' => true,
            ]);

        $user->refresh();

        $this->assertTrue(Storage::disk('public')->exists($user->profile_image_url));
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    public function test_updateProfileImage_validation_required_data()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [];
        $errors = [
            'profile_image' => 'The profile image field is required.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.profileImage'), $data, $errors);
    }

    public function test_updateProfileImage_validation_profile_image_has_mime_type()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);
        $profileImage = UploadedFile::fake()->image('profile.mp4');

        $data = [
            'profile_image' => $profileImage,
        ];
        $errors = [
            'profile_image' => 'The profile image must be a file of type: jpeg, jpg, png.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.profileImage'), $data, $errors);
    }

    public function test_updateProfileImage_validation_profile_image_has_max_1023()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);
        $profileImage = UploadedFile::fake()->create('image.png', 1024);

        $data = [
            'profile_image' => $profileImage,
        ];
        $errors = [
            'profile_image' => 'The profile image must not be greater than 1023 kilobytes.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.update.profileImage'), $data, $errors);
    }
}