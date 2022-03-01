<?php

namespace Tests\Feature\Controllers\Api\Comment;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\Feature\Controllers\Api\Post\AddComment;
use Tests\Feature\Controllers\Api\Post\CreatePost;
use Tests\TestCase;

class LikeMethodCommentControllerTest extends TestCase
{
    use RefreshDatabase, CreatePost;

    /*
     *  test_execute_method_dispatch_event
     *  test_execute_method_when_user_not_logged_in
     *  test_execute_method_user_cannot_like_more_than_once
     */


    private function addComment($post)
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->postJson(route('api.posts.comment', $post->id), ['text' => Comment::factory()->make()['text']]);
    }

    private function can(User $postOwner, User $liker)
    {
        $post = $this->createPost($postOwner, 1);
        $this->addComment($post);
        $comment = $post->comments()->first();

        $this->actingAs($liker);

        $this->postJson(route('api.comments.like', $comment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Comment liked successfully.',
                'success' => true,
            ]);

        $liker->refresh();
        $comment->refresh();

        $this->assertCount(1, $comment->likes);
        $this->assertEquals(1, $comment->like_count);
        $this->assertEquals($liker->id, $comment->likes->first()->user->id);
        $this->assertCount(1, $liker->likes);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(User $postOwner, User $liker)
    {
        $post = $this->createPost($postOwner, 1);
        $this->addComment($post);
        $comment = $post->comments()->first();

        $this->actingAs($liker);

        $this->postJson(route('api.comments.like', $comment->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_like_method()
    {
        $this->can(
            User::factory()->user()->publicAccount()->create(),
            User::factory()->user()->create()
        );
    }

    public function test_if_comment_for_private_user_post_and_not_followed_cannot_like_it()
    {
        $this->cannot(
            User::factory()->user()->privateAccount()->create(),
            User::factory()->user()->create()
        );
    }

    public function test_private_user_can_like_own_post_comment()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $this->can($postOwner, $postOwner);
    }

    public function test_user_can_like_private_user_post_comment_he_has_followed()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $postOwner->id));

        $this->actingAs($postOwner)
            ->postJson(route('api.acceptFollowRequest', $me->id));

        $this->can($postOwner, $me);
    }

    public function test_user_cannot_like_private_user_post_comment_he_has_requested()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $postOwner->id));

//        $this->actingAs($postOwner)
//            ->postJson(route('api.acceptFollowRequest', $me->id));

        $this->cannot($postOwner, $me);
    }
}
