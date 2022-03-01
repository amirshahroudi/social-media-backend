<?php

namespace Tests\Feature\Actions\Post;

use App\Actions\Post\CommentPostAction;
use App\Events\Post\CommentedPostEvent;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CommentPostActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        $post = Post::factory()->create();

        $user = User::factory()->create();

        $comment = Comment::factory()
            ->for($user)
            ->for($post)
            ->make()
            ->toArray();

        $text = $comment['text'];

        $this->actingAs($user);

        $action = new CommentPostAction();
        $action->execute($post, $text);

        $post->refresh();

        $this->assertDatabaseHas('comments', $comment);
        $this->assertCount(1, $post->comments);
        $this->assertEquals($comment['text'], $post->comments->first()->text);
        $this->assertEquals($user->id, $post->comments->first()->user->id);
        $this->assertEquals($user->id, Comment::where($comment)->first()->user->id);
        $this->assertEquals(1, $post->comment_count);
    }
    public function test_execute_method_dispatch_even(){
        Event::fake();

        $post = Post::factory()->create();

        $user = User::factory()->create();

        $comment = Comment::factory()
            ->for($user)
            ->for($post)
            ->make()
            ->toArray();

        $text = $comment['text'];

        $this->actingAs($user);

        $action = new CommentPostAction();
        $action->execute($post, $text);

        Event::assertDispatched(CommentedPostEvent::class);
    }
    public function test_execute_method_when_user_not_logged_in()
    {
        $post = Post::factory()->create();

        $comment = Comment::factory()
            ->for($post)
            ->make()
            ->toArray();

        $text = $comment['text'];

        $action = new CommentPostAction();

        $catchException = false;

        try {
            $action->execute($post, $text);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }

        $post->refresh();

        $this->assertTrue($catchException);
        $this->assertDatabaseCount('comments', 0);
        $this->assertCount(0, $post->comments);
        $this->assertEquals(0, $post->comment_count);
    }
}
