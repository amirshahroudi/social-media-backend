<?php

namespace Tests\Feature\Actions\Comment;

use App\Actions\Comment\LikeCommentAction;
use App\Actions\Comment\UnlikeCommentAction;
use App\Events\Comment\UnlikedCommentEvent;
use App\Exceptions\LikeException;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UnlikeCommentActionTest extends TestCase
{
    use RefreshDatabase;

    //test execute method
    //test if user not logged in
    //test if user didnt like comment

    public function test_execute_method()
    {
        $comment = Comment::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->app->make(LikeCommentAction::class)->execute($comment);

        $action = new UnlikeCommentAction();
        $action->execute($comment);

        $comment->refresh();

        $this->assertCount(0, $comment->likes);
        $this->assertEquals(0, $comment->like_count);
        $this->assertEmpty($user->likes);
        $this->assertDatabaseCount('likes', 0);
    }

    public function test_execute_method_dispatch_event()
    {
        Event::fake();

        $comment = Comment::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->app->make(LikeCommentAction::class)->execute($comment);

        $action = new UnlikeCommentAction();
        $action->execute($comment);

        Event::assertDispatched(UnlikedCommentEvent::class);
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        $comment = Comment::factory()->create();

        $action = new UnlikeCommentAction();

        $catchException = false;

        try {
            $action->execute($comment);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }

        $this->assertTrue($catchException);
    }

    public function test_execute_method_when_user_not_liked_before()
    {
        $comment = Comment::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $action = new UnlikeCommentAction();

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

        $this->assertTrue($catchException);
        $this->assertEquals(LikeException::USER_DIDNT_LIKE_COMMENT_BEFORE, $exceptionMessage);
        $this->assertEquals(LikeException::USER_DIDNT_LIKE_COMMENT_BEFORE_STATUS_CODE, $exceptionCode);
    }
}
