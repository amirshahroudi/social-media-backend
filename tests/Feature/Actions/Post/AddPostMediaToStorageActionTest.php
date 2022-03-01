<?php

namespace Tests\Feature\Actions\Post;

use App\Actions\Post\AddPostMediaToStorageAction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AddPostMediaToStorageActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        $user_id = 'test-user-id';
        $post_id = 'test-post-id';
        $mediaName = 'media_1.jpg';
        $media = UploadedFile::fake()->image($mediaName);


        $action = new AddPostMediaToStorageAction();
        $path = $action->execute($media, $mediaName, $user_id, $post_id);

        $this->assertEquals("users/{$user_id}/posts/{$post_id}/{$mediaName}", $path);
        $this->assertTrue(Storage::exists($path));
    }
}
