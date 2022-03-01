<?php

namespace Tests\Feature\Controllers\Api\Post;

use App\Actions\Post\LikePostAction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UnlikeMethodTest extends TestCase
{
    use RefreshDatabase, CreatePost;

    /* tests in actions:
     *   test_execute_method_dispatch_event
     *   test_execute_method_when_user_not_liked_before
     */

    private function can(User $postOwner, User $unliker)
    {
        $post = $this->createPost($postOwner, 1);

        $this->actingAs($unliker);

        $this->app->make(LikePostAction::class)->execute($post);

        $unliker->refresh();

        $this->postJson(route('api.posts.unlike', $post))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Post unliked successfully.',
                'success' => true,
            ]);

        $post->refresh();
        $unliker->refresh();

        $this->assertCount(0, $post->likes);
        $this->assertEquals(0, $post->like_count);
        $this->assertEmpty($unliker->likes);
        $this->assertDatabaseCount('likes', 0);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(User $postOwner, User $unliker)
    {
        $post = $this->createPost($postOwner, 1);

        $this->actingAs($unliker);

        $this->app->make(LikePostAction::class)->execute($post);

        $this->postJson(route('api.posts.unlike', $post))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_unlike_method()
    {
        $this->can(User::factory()->publicAccount()->create(), User::factory()->user()->create());
    }

    public function test_if_post_for_private_user_and_not_followed_cannot_unlike_it()
    {
        $this->cannot(User::factory()->privateAccount()->create(), User::factory()->user()->create());
    }

    public function test_private_user_can_unlike_own_post()
    {
        $postOwner = User::factory()->privateAccount()->create();

        $this->can($postOwner, $postOwner);
    }

    public function test_user_can_unlike_private_user_post_he_has_followed()
    {
        $postOwner = User::factory()->privateAccount()->create();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $postOwner->id));

        $this->actingAs($postOwner)
            ->postJson(route('api.acceptFollowRequest', $me->id));

        $me->refresh();
        $postOwner->refresh();

        $this->can($postOwner, $me);
    }

    public function test_user_cannot_unlike_private_user_post_he_has_requested()
    {
        $postOwner = User::factory()->privateAccount()->create();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $postOwner->id));

//        $this->actingAs($postOwner)
//            ->postJson(route('api.acceptFollowRequest', $me->id));

        $me->refresh();
        $postOwner->refresh();

        $this->cannot($postOwner, $me);
    }
}
