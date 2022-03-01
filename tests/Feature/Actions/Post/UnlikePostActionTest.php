<?php

namespace Tests\Feature\Actions\Post;

use App\Actions\Post\LikePostAction;
use App\Actions\Post\UnlikePostAction;
use App\Events\Post\UnlikedPostEvent;
use App\Exceptions\LikeException;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use phpDocumentor\Reflection\Types\This;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UnlikePostActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        $post = Post::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->app->make(LikePostAction::class)->execute($post);

        $action = new UnlikePostAction();
        $action->execute($post);

        $post->refresh();

        $this->assertCount(0, $post->likes);
        $this->assertEquals(0, $post->like_count);
        $this->assertEmpty($user->likes);
        $this->assertDatabaseCount('likes', 0);
    }

    public function test_execute_method_dispatch_event()
    {
        Event::fake();

        $post = Post::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->app->make(LikePostAction::class)->execute($post);

        $action = new UnlikePostAction();
        $action->execute($post);
        Event::assertDispatched(UnlikedPostEvent::class);
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        $post = Post::factory()->create();

        $action = new UnlikePostAction();

        $catchException = false;

        try {
            $action->execute($post);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }

        $this->assertTrue($catchException);
    }

    public function test_execute_method_when_user_not_liked_before()
    {
        $post = Post::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $action = new UnlikePostAction();

        $catchException = false;
        $exceptionMessage = '';
        $exceptionCode = '';

        try {
            $action->execute($post);
        } catch (LikeException $exception) {
            $catchException = true;
            $exceptionMessage = $exception->getMessage();
            $exceptionCode = $exception->getCode();
        }

        $this->assertTrue($catchException);
        $this->assertEquals(LikeException::USER_DIDNT_LIKE_POST_BEFORE, $exceptionMessage);
        $this->assertEquals(LikeException::USER_DIDNT_LIKE_POST_BEFORE_STATUS_CODE, $exceptionCode);
    }
}
