<?php

namespace Tests\Feature\Actions\Comment;

use App\Actions\Comment\LikeCommentAction;
use App\Events\Comment\LikedCommentEvent;
use App\Exceptions\LikeException;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LikeCommentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        $comment = Comment::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $action = new LikeCommentAction();
        $action->execute($comment);

        $user->refresh();
        $comment->refresh();

        $this->assertCount(1, $comment->likes);
        $this->assertEquals(1, $comment->like_count);
        $this->assertEquals($user->id, $comment->likes->first()->user->id);
        $this->assertCount(1, $user->likes);
    }

    public function test_execute_method_dispatch_event()
    {
        Event::fake();

        $comment = Comment::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $action = new LikeCommentAction();
        $action->execute($comment);

        Event::assertDispatched(LikedCommentEvent::class);
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        $comment = Comment::factory()->create();

        $action = new LikeCommentAction();

        $catchException = false;
        try {
            $action->execute($comment);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }

        $comment->refresh();

        $this->assertTrue($catchException);
        $this->assertCount(0, $comment->likes);
        $this->assertEquals(0, $comment->like_count);
    }

    public function test_execute_method_user_cannot_like_more_than_once()
    {
        $comment = Comment::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $action = new LikeCommentAction();
        $action->execute($comment);

        $catchException = false;
        $exceptionMessage = '';
        $exceptionCode = '';

        try {
            $action->execute($comment);
        } catch (LikeException $exception) {
            $catchException = true;
            $exceptionMessage = $exception->getMessage();
            $exceptionCode = $exception->getCode();
        }

        $user->refresh();
        $comment->refresh();

        $this->assertTrue($catchException);
        $this->assertEquals(LikeException::USER_ALREADY_LIKED_COMMENT, $exceptionMessage);
        $this->assertEquals(LikeException::USER_ALREADY_LIKED_COMMENT_STATUS_CODE, $exceptionCode);
        $this->assertCount(1, $comment->likes);
        $this->assertEquals(1, $comment->like_count);
        $this->assertEquals($user->id, $comment->likes->first()->user->id);
        $this->assertCount(1, $user->likes);
    }
}
