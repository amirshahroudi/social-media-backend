<?php

namespace Tests\Feature\Actions\Post;

use App\Actions\Post\DestroyPostAction;
use App\Events\Post\DestroyedPostEvent;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DestroyPostActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        $commentCount = rand(1, 20);
        $likeCount = rand(1, 20);
        $post = Post::factory()
            ->for($user = User::factory()->create())
            ->has(Comment::factory()->count($commentCount))
            ->has(Like::factory()->count($likeCount))
            ->create();

        $postId = $post->id;

        $this->actingAs($user);

        $action = new DestroyPostAction();
        $action->execute($post);

        $user->refresh();

        $this->assertDeleted($post)
            ->assertDeleted('comments', ['post_id' => $postId])
            ->assertDeleted('likes', ['likable_id' => $postId, 'likable_type' => get_class($post)]);

        $this->assertEquals(0, $user->post_count);
        $this->assertEmpty(DB::select('select * from posts where id = :postId', ['postId' => $postId]));
        $this->assertEmpty(DB::select('select * from comments where post_id = :postId', ['postId' => $postId]));
        $this->assertEmpty(
            DB::select(
                'select * from likes where likable_id = :postId and likable_type = :postType',
                ['postId' => $postId, 'postType' => get_class(new Post())])
        );
    }

    public function test_execute_method_dispatch_event()
    {
        Event::fake();

        $commentCount = rand(1, 20);
        $likeCount = rand(1, 20);
        $post = Post::factory()
            ->for($user = User::factory()->create())
            ->has(Comment::factory()->count($commentCount))
            ->has(Like::factory()->count($likeCount))
            ->create();

        $this->actingAs($user);

        $action = new DestroyPostAction();
        $action->execute($post);
        Event::assertDispatchedTimes(DestroyedPostEvent::class);
    }
}