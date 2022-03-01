<?php

namespace Tests\Feature\Controllers\Api\Post;

use App\Events\Post\DestroyedPostEvent;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DestroyMethodTest extends TestCase
{
    use RefreshDatabase;

    private function createPost(User $postOwner)
    {
        $commentCount = rand(1, 20);
        $likeCount = rand(1, 20);
        $post = Post::factory()
            ->for($postOwner)
            ->has(Comment::factory()->count($commentCount))
            ->has(Like::factory()->count($likeCount))
            ->create();

        return $post;
    }

    private function can(User $postOwner, User $destroyer)
    {
        $post = $this->createPost($postOwner);

        $this->actingAs($destroyer);

        $postId = $post->id;
        $this->deleteJson(route('api.posts.destroy', $post))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Post destroyed successfully.",
                'success' => true,
            ]);

        $destroyer->refresh();

        $this->assertDeleted($post)
            ->assertDeleted('comments', ['post_id' => $postId])
            ->assertDeleted('likes', ['likable_id' => $postId, 'likable_type' => get_class($post)]);

        $this->assertEquals(0, $destroyer->post_count);
        $this->assertEmpty(DB::select('select * from posts where id = :postId', ['postId' => $postId]));
        $this->assertEmpty(DB::select('select * from comments where post_id = :postId', ['postId' => $postId]));
        $this->assertEmpty(
            DB::select(
                'select * from likes where likable_id = :postId and likable_type = :postType',
                ['postId' => $postId, 'postType' => get_class(new Post())])
        );
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(User $postOwner, User $destroyer)
    {
        $commentCount = rand(1, 20);
        $likeCount = rand(1, 20);
        $post = Post::factory()
            ->for($postOwner)
            ->has(Comment::factory()->count($commentCount))
            ->has(Like::factory()->count($likeCount))
            ->create();

        $postId = $post->id;

        $this->actingAs($destroyer);

        $this->deleteJson(route('api.posts.destroy', $post))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $postOwner->refresh();

        $this->assertDatabaseHas('posts', ['caption' => $post->caption])
            ->assertDatabaseHas('comments', ['post_id' => $postId])
            ->assertDatabaseHas('likes', ['likable_id' => $postId, 'likable_type' => get_class($post)]);

        $this->assertEquals(1, $postOwner->post_count);
        $this->assertCount(1, DB::select('select * from posts where id = :postId', ['postId' => $postId]));
        $this->assertCount($commentCount, DB::select('select * from comments where post_id = :postId', ['postId' => $postId]));
        $this->assertCount(
            $likeCount,
            DB::select(
                'select * from likes where likable_id = :postId and likable_type = :postType',
                ['postId' => $postId, 'postType' => get_class(new Post())])
        );
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    public function test_destroy_method()
    {
        $postOwner = User::factory()->user()->create();
        $this->can($postOwner, $postOwner);
    }

    public function test_destroy_method_dispatch_event()
    {
        Event::fake();

        $post = $this->createPost($user = User::factory()->create());

        $this->actingAs($user);

        $this->deleteJson(route('api.posts.destroy', $post));

        Event::assertDispatchedTimes(DestroyedPostEvent::class);
    }

    public function test_if_user_not_post_owner_cannot_destroy_post()
    {
        $this->cannot(User::factory()->user()->create(), User::factory()->user()->create());
    }

    public function test_if_user_is_admin_can_destroy_post()
    {
        $this->can(User::factory()->user()->create(), User::factory()->admin()->create());
    }
}
