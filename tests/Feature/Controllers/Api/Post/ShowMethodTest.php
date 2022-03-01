<?php

namespace Tests\Feature\Controllers\Api\Post;

use App\Actions\Post\CommentPostAction;
use App\Actions\Post\LikePostAction;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use phpDocumentor\Reflection\Types\This;
use Tests\TestCase;

class ShowMethodTest extends TestCase
{
    use RefreshDatabase, CreatePost;

    private function createPostAndAddOtherThingsToIt(User $postOwner)
    {
        $likeCount = rand(1, 10);
        $commentCount = rand(1, 10);
        $mediaCount = rand(1, 10);

        $post = $this->createPost($postOwner, $mediaCount);

        $this->addComment($commentCount, $post);
        $this->addLike($likeCount, $post);

        $post->refresh();
        return $post;
    }

    private function addComment($commentCount, $post)
    {
        for ($i = 0; $i < $commentCount; $i++) {
            $this->actingAs(User::factory()->create());
            $this->postJson(route('api.posts.comment', $post->id), ['text' => Comment::factory()->make()['text']]);
        }
    }

    private function addLike($likeCount, $post)
    {
        for ($i = 0; $i < $likeCount; $i++) {
            $this->actingAs(User::factory()->create());
            $this->postJson(route('api.posts.like', $post->id));
        }
    }

    private function can(Post $post, User $requester)
    {
        $this->actingAs($requester);

        $this->getJson(route('api.posts.show', $post->id))
            ->assertJson([
                'data'    => [
                    'id'            => $post->id,
                    'username'      => $post->user->username,
                    'caption'       => $post->caption,
                    'medias_count'  => $post->postMedias()->count(),
                    'created_at'    => (string)$post->created_at,
                    'like_count'    => $post->like_count,
                    'comment_count' => $post->comment_count,
                ],
                'success' => true,
            ])
            ->assertStatus(Response::HTTP_OK);

        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(Post $post, User $requester)
    {
        $this->actingAs($requester);

        $this->getJson(route('api.posts.show', $post->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_show_method()
    {
        $postOwner = User::factory()->user()->publicAccount()->create();
        $post = $this->createPostAndAddOtherThingsToIt($postOwner);
        $this->can($post, User::factory()->user()->create());
    }

    public function test_if_post_for_private_user_and_not_followed_cannot_see_it()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $post = $this->createPostAndAddOtherThingsToIt($postOwner);
        $this->cannot($post, User::factory()->user()->create());
    }

    public function test_private_user_can_see_own_post()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $post = $this->createPostAndAddOtherThingsToIt($postOwner);
        $this->can($post, $postOwner);
    }

    public function test_user_can_see_private_user_post_he_has_followed()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $post = $this->createPostAndAddOtherThingsToIt($postOwner);

        $me = User::factory()->user()->create();
        $this->actingAs($me)
            ->postJson(route('api.follow', $postOwner->id));
        $this->actingAs($postOwner)
            ->postJson(route('api.acceptFollowRequest', $me->id));
        $me->refresh();
        $postOwner->refresh();

        $this->can($post, $me);
    }

    public function test_user_cannot_see_private_user_post_he_has_requested()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $post = $this->createPostAndAddOtherThingsToIt($postOwner);

        $me = User::factory()->user()->create();
        $this->actingAs($me)
            ->postJson(route('api.follow', $postOwner->id));
//        $this->actingAs($postOwner)
//            ->postJson(route('api.acceptFollowRequest', $me->id));
        $me->refresh();
        $postOwner->refresh();

        $this->cannot($post, $me);
    }

    public function test_admin_has_access()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $post = $this->createPostAndAddOtherThingsToIt($postOwner);;
        $this->can($post, User::factory()->admin()->create());
    }
}
