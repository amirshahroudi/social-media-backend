<?php

namespace Tests\Feature\Actions\Comment;

use App\Actions\Comment\DestroyCommentAction;
use App\Events\Comment\DestroyedCommentEvent;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DestroyCommentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        $commentCount = rand(1, 10);
        $post = Post::factory()->create();

        Comment::factory()
            ->for($post)
            ->count($commentCount)
            ->create();

        $commentToDelete = Comment::factory()
            ->for($post)
            ->has(Like::factory()->count(rand(1, 10)))
            ->create();

        $action = new DestroyCommentAction();
        $action->execute($commentToDelete);

        $post->refresh();

        $this->assertDatabaseCount('comments', $commentCount);
        $this->assertDatabaseCount('likes', 0);
        $this->assertDatabaseMissing('comments', $commentToDelete->toArray());
        $this->assertEquals($commentCount, $post->comment_count);
        $this->assertCount($commentCount, $post->comments);
    }

    public function test_execute_method_dispatch_event()
    {
        Event::fake();

        $commentCount = rand(1, 10);
        $post = Post::factory()->create();

        Comment::factory()
            ->for($post)
            ->count($commentCount)
            ->create();

        $commentToDelete = Comment::factory()
            ->for($post)
            ->has(Like::factory()->count(rand(1, 10)))
            ->create();

        $action = new DestroyCommentAction();
        $action->execute($commentToDelete);

        Event::assertDispatched(DestroyedCommentEvent::class);
    }
}
