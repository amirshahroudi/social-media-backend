<?php

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\UpdateProfileImageAction;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateProfileImageActionTest extends TestCase
{
    use RefreshDatabase;

    /*
     * 1 --> test execute method
     * 2 --> test when user not logged in
     * 3 --> if profile exists remove old photo after change it
     */

    public function test_execute_method()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $profileImage = UploadedFile::fake()->image('profile.jpg');

        $action = new UpdateProfileImageAction();
        $action->execute($profileImage);

        $user->refresh();

        $this->assertTrue(Storage::disk('public')->exists($user->profile_image_url));
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        $profileImage = UploadedFile::fake()->image('profile.jpg');

        $action = new UpdateProfileImageAction();

        $catchException = false;

        try {
            $action->execute($profileImage);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }

        $this->assertTrue($catchException);
    }

    public function test_execute_method_if_user_have_already_had_profile_image()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $oldProfileImage = UploadedFile::fake()->image('profile.jpg');

        $action = new UpdateProfileImageAction();
        $action->execute($oldProfileImage);

        $user->refresh();

        $oldProfileImagePath = $user->profile_image_url;

        $newProfileImage = UploadedFile::fake()->image('new.jpg');

        $action->execute($newProfileImage);

        $user->refresh();

        $this->assertTrue(Storage::disk('public')->exists($user->profile_image_url));
        $this->assertFalse(Storage::disk('public')->exists($oldProfileImagePath));
        $this->assertNotEquals($oldProfileImagePath, $user->profile_image_url);
    }
}