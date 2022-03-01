<?php

namespace Tests\Feature\Controllers\Api\Comment;

use App\Actions\Comment\LikeCommentAction;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\Feature\Controllers\Api\Post\CreatePost;
use Tests\TestCase;

class UnlikeMethodCommentControllerTest extends TestCase
{
    use RefreshDatabase, CreatePost;

    /*
     *  test_execute_method_dispatch_event
     *  test_execute_method_when_user_not_logged_in
     *  test_execute_method_when_user_not_liked_before
     */

    private function addComment($post)
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->postJson(route('api.posts.comment', $post->id), ['text' => Comment::factory()->make()['text']]);
    }

    private function can(User $postOwner, User $unliker)
    {
        $post = $this->createPost($postOwner, 1);
        $this->addComment($post);
        $post->refresh();

        $this->actingAs($unliker);
        $this->app->make(LikeCommentAction::class)->execute($comment = $post->comments()->first());

        $this->postJson(route('api.comments.unlike', $comment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Comment unliked successfully.',
                'success' => true,
            ]);

        $comment->refresh();

        $this->assertCount(0, $comment->likes);
        $this->assertEquals(0, $comment->like_count);
        $this->assertEmpty($unliker->likes);
        $this->assertDatabaseCount('likes', 0);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(User $postOwner, User $unliker)
    {
        $post = $this->createPost($postOwner, 1);
        $this->addComment($post);
        $post->refresh();

        $this->actingAs($unliker);
        $this->app->make(LikeCommentAction::class)->execute($comment = $post->comments()->first());

        $this->postJson(route('api.comments.unlike', $comment->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_unlike_method()
    {
        $this->can(
            User::factory()->user()->publicAccount()->create(),
            User::factory()->user()->create()
        );
    }

    public function test_if_comment_for_private_user_post_and_unfollowed_cannot_unlike_it()
    {
        $this->cannot(
            User::factory()->user()->privateAccount()->create(),
            User::factory()->user()->create()
        );
    }

    public function test_private_user_can_unlike_own_post_comment()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $this->can($postOwner, $postOwner);
    }

    public function test_user_can_unlike_private_user_post_comment_he_has_followed()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $me = User::factory()->user()->create();

        $this->actingAs($me)
            ->postJson(route('api.follow', $postOwner->id));

        $this->actingAs($postOwner)
            ->postJson(route('api.acceptFollowRequest', $me->id));

        $this->can($postOwner, $me);
    }

    public function test_user_cannot_unlike_private_user_post_comment_he_has_requested()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $me = User::factory()->user()->create();

        $this->actingAs($me)
            ->postJson(route('api.follow', $postOwner->id));

//        $this->actingAs($postOwner)
//            ->postJson(route('api.acceptFollowRequest', $me->id));

        $this->cannot($postOwner, $me);
    }
}
