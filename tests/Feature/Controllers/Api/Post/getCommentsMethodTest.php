<?php

namespace Tests\Feature\Controllers\Api\Post;

use App\Actions\Post\CommentPostAction;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class getCommentsMethodTest extends TestCase
{
    use RefreshDatabase, CreatePost;

    private function can(User $postOwner, User $requester)
    {
        $commentCount = rand(10, 30);
        $mediaCount = rand(1, 10);

        $post = $this->createPost($postOwner, $mediaCount);

        $this->addComment($commentCount, $post);

        $post->refresh();

        $comments = $post->comments()->latest()->get(['id', 'user_id', 'post_id', 'text', 'like_count', 'created_at']);

        $commentShouldSee = $comments[rand(0, 9)]->toArray();
        unset($commentShouldSee['created_at']);

        $this->getJson(route('api.posts.comments', $post->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data'  => [
                    '*' => ['id', 'user_id', 'post_id', 'text', 'like_count', 'created_at'],
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta'  => [
                    'current_page',
                    'from',
                    'last_page', 'links',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ])
            ->assertSee($commentShouldSee, false)
            ->assertSee(['total' => $commentCount])
            ->assertSee(['per_page' => 10]);

        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(User $postOwner, User $requester)
    {
        $commentCount = rand(10, 30);
        $mediaCount = rand(1, 10);

        $post = $this->createPost($postOwner, $mediaCount);

        $this->addComment($commentCount, $post);

        $post->refresh();

        $this->actingAs($requester);

        $this->getJson(route('api.posts.comments', $post->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function addComment($commentCount, $post)
    {
        for ($i = 0; $i < $commentCount; $i++) {
            $this->travel(10 + $i * 10)->minutes();
            $this->actingAs(User::factory()->admin()->create());
//            $this->app->make(CommentPostAction::class)->execute($post->id, ['text' => Comment::factory()->make()['text']]);
            $this->postJson(route('api.posts.comment', $post->id), ['text' => Comment::factory()->make()['text']]);
        }
    }

    public function test_getComments_method()
    {
        $this->can(User::factory()->user()->publicAccount()->create(), User::factory()->user()->create());
    }

    public function test_if_post_for_private_user_and_not_followed_cannot_see_its_comments()
    {
        $this->cannot(User::factory()->user()->privateAccount()->create(), User::factory()->user()->create());
    }

    public function test_private_user_can_see_own_post_comments()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $this->can($postOwner, $postOwner);
    }

    public function test_user_can_see_private_user_post_comments_he_has_followed()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $postOwner->id));

        $this->actingAs($postOwner)
            ->postJson(route('api.acceptFollowRequest', $me->id));

        $me->refresh();
        $postOwner->refresh();

        $this->can($postOwner, $me);
    }

    public function test_user_cannot_see_private_user_post_comments_he_has_requested()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $postOwner->id));

//        $this->actingAs($postOwner)
//            ->postJson(route('api.acceptFollowRequest', $me->id));

        $me->refresh();
        $postOwner->refresh();

        $this->cannot($postOwner, $me);
    }
}
