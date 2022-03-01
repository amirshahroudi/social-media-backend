<?php

namespace Tests\Feature\Controllers\Api\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LikeMethodTest extends TestCase
{
    use RefreshDatabase, CreatePost;

    /*
     *  test user can only like post once in action
     *  test dispatch event in action
     */

    private function can(User $postOwner, User $liker)
    {
        $post = $this->createPost($postOwner, 1);

        $this->actingAs($liker);

        $this->postJson(route('api.posts.like', $post))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Post liked successfully.',
                'success' => true,
            ]);

        $post->refresh();

        $this->assertCount(1, $post->likes);
        $this->assertEquals(1, $post->like_count);
        $this->assertEquals($liker->id, $post->likes->first()->user->id);
        $this->assertCount(1, $liker->likes);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(User $postOwner, User $unliker)
    {
        $post = $this->createPost($postOwner, 1);

        $this->actingAs($unliker);

        $this->postJson(route('api.posts.like', $post))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_like_method()
    {
        $this->can(User::factory()->publicAccount()->create(), User::factory()->create());
    }

    public function test_if_post_for_private_user_and_not_followed_cannot_like_it()
    {
        $this->cannot(User::factory()->privateAccount()->create(), User::factory()->user()->create());
    }

    public function test_private_user_can_like_own_post()
    {
        $postOwner = User::factory()->privateAccount()->create();

        $this->can($postOwner, $postOwner);
    }

    public function test_user_can_like_private_user_post_he_has_followed()
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

    public function test_user_cannot_like_private_user_post_he_has_requested()
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
