<?php

namespace Tests\Feature\Controllers\Api\Comment;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class DestroyMethodCommentControllerTest extends TestCase
{
    use RefreshDatabase;

    /*
     *  test_execute_method_dispatch_event
     */

    private function can(User $postOwner, User $commentOwner, User $destroyer)
    {
        $commentCount = rand(1, 10);
        $post = Post::factory()
            ->for($postOwner)
            ->create();

        Comment::factory()
            ->for($post)
            ->count($commentCount)
            ->create();

        $commentToDelete = Comment::factory()
            ->for($commentOwner)
            ->for($post)
            ->has(Like::factory()->count(rand(1, 10)))
            ->create();

        $this->actingAs($destroyer);

        $this->deleteJson(route('api.comments.destroy', $commentToDelete))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Comment destroyed successfully.',
                'success' => true,
            ]);

        $post->refresh();

        $this->assertDatabaseCount('comments', $commentCount);
        $this->assertDatabaseCount('likes', 0);
        $this->assertDatabaseMissing('comments', $commentToDelete->toArray());
        $this->assertEquals($commentCount, $post->comment_count);
        $this->assertCount($commentCount, $post->comments);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(User $postOwner, User $commentOwner, User $destroyer)
    {
        $commentCount = rand(1, 10);
        $post = Post::factory()
            ->for($postOwner)
            ->create();

        Comment::factory()
            ->for($post)
            ->count($commentCount)
            ->create();

        $commentToDelete = Comment::factory()
            ->for($commentOwner)
            ->for($post)
            ->has(Like::factory()->count($likeCount = rand(1, 10)))
            ->create();

        $this->actingAs($destroyer);

        $this->deleteJson(route('api.comments.destroy', $commentToDelete))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $post->refresh();

        $this->assertDatabaseCount('comments', ($commentCount + 1));
        $this->assertDatabaseCount('likes', $likeCount);
        $this->assertDatabaseHas('comments', ['id' => $commentToDelete->id]);
        $this->assertEquals(($commentCount + 1), $post->comment_count);
        $this->assertCount(($commentCount + 1), $post->comments);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    public function test_destroy_method()
    {
        $this->can(
            User::factory()->user()->publicAccount()->create(),
            $commentOwner = User::factory()->user()->create(),
            $commentOwner
        );
    }

    public function test_if_user_not_comment_owner_cannot_destroy_comment()
    {
        $this->cannot(
            User::factory()->user()->create(),
            User::factory()->user()->create(),
            User::factory()->user()->create()
        );
    }

    public function test_if_user_is_post_owner()
    {
        $this->can(
            $postOwner = User::factory()->user()->privateAccount()->create(),
            User::factory()->user()->create(),
            $postOwner
        );
    }

    public function test_if_user_is_comment_owner()
    {
        $this->can(
            User::factory()->user()->privateAccount()->create(),
            $commentOwner = User::factory()->user()->create(),
            $commentOwner
        );
    }

    public function test_if_user_is_admin()
    {
        $this->can(
            User::factory()->user()->privateAccount()->create(),
            User::factory()->user()->create(),
            User::factory()->admin()->create()
        );
    }

    //todo decide if post for private user that unfollowed can destroy or not
}
