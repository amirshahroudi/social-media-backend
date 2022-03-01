<?php

namespace Tests\Feature\Controllers\Api\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class getProfileImageMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_getProfileImage_method()
    {
        $owner = User::factory()->user()->create();
        $this->actingAs($owner);

        $profileImage = UploadedFile::fake()->image('profile.jpg', 124, 241);

        $this->postJson(route('api.update.profileImage'), ['profile_image' => $profileImage])
            ->assertStatus(Response::HTTP_OK);

        $owner->refresh();

        $image = Storage::disk('public')->get($owner->profile_image_url);

        $this->actingAs(User::factory()->user()->create());

        $response = $this->getJson(route('api.users.profile', $owner->username))
            ->assertStatus(Response::HTTP_OK);

        $this->assertEquals($image, $response->getContent());
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    public function test_if_profile_image_is_null()
    {
        $owner = User::factory()->user()->create();
        $this->actingAs($owner);

        $this->actingAs(User::factory()->user()->create());

        $response = $this->getJson(route('api.users.profile', $owner->username))
            ->assertStatus(Response::HTTP_OK);

        $this->assertEquals(null, $response->getContent());
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }
}
