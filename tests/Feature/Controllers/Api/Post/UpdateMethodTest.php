<?php

namespace Tests\Feature\Controllers\Api\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class UpdateMethodTest extends TestCase
{
    use  RefreshDatabase, JsonRequestForValidation;

    public function test_update_method()
    {
        $post = Post::factory()
            ->for($user = User::factory()->create())
            ->create();

        $oldPost = $post->toArray();
        unset($oldPost['created_at']);
        unset($oldPost['updated_at']);

        $data = Post::factory()->make()->toArray();
        $newCaption = $data['caption'];

        $this->actingAs($user);

        $this->patchJson(route('api.posts.update', $post), ['caption' => $newCaption])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Post updated successfully.',
                'success' => true,
            ]);

        $post->refresh();

        $this->assertEquals($newCaption, $post->caption);
        $this->assertDatabaseMissing('posts', $oldPost);
        $this->assertDatabaseCount('posts', 1);
        $this->assertDatabaseHas('posts', ['caption' => $newCaption]);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    public function test_update_validation_required_data()
    {
        $post = Post::factory()
            ->for($user = User::factory()->create())
            ->create();

        $this->actingAs($user);

        $data = [];
        $errors = [
            'caption' => 'The caption field is required.',
        ];
        $this->sendPatchJsonRequestForValidation(route('api.posts.update', $post), $data, $errors);
    }

    public function test_update_validation_caption_has_string_rule()
    {
        $post = Post::factory()
            ->for($user = User::factory()->create())
            ->create();

        $this->actingAs($user);

        $data = [
            'caption' => 123,
        ];
        $errors = [
            'caption' => 'The caption must be a string.',
        ];
        $this->sendPatchJsonRequestForValidation(route('api.posts.update', $post), $data, $errors);
    }

    public function test_update_validation_caption_has_max_2047_rule()
    {
        $post = Post::factory()
            ->for($user = User::factory()->create())
            ->create();

        $this->actingAs($user);

        $data = [
            'caption' => Str::random(2048),
        ];
        $errors = [
            'caption' => 'The caption must not be greater than 2047 characters.',
        ];
        $this->sendPatchJsonRequestForValidation(route('api.posts.update', $post), $data, $errors);
    }

    public function test_if_user_not_post_owner_cannot_update_post()
    {
        $post = Post::factory()
            ->for($postOwner = User::factory()->create())
            ->create();

        $oldPost = $post->toArray();
        unset($oldPost['created_at']);
        unset($oldPost['updated_at']);

        $data = Post::factory()->make()->toArray();
        $newCaption = $data['caption'];

        $this->actingAs(User::factory()->user()->create());

        $this->patchJson(route('api.posts.update', $post), ['caption' => $newCaption])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertNotEquals($newCaption, $post->caption);
        $this->assertDatabaseHas('posts', $oldPost);
        $this->assertDatabaseCount('posts', 1);
        $this->assertDatabaseMissing('posts', ['caption' => $newCaption]);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }
}