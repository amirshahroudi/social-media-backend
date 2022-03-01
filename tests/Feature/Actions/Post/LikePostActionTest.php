<?php

namespace Tests\Feature\Actions\Post;

use App\Actions\Post\LikePostAction;
use App\Events\Post\LikedPostEvent;
use App\Exceptions\LikeException;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LikePostActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        $post = Post::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $action = new LikePostAction();
        $action->execute($post);

        $post->refresh();

        $this->assertCount(1, $post->likes);
        $this->assertEquals(1, $post->like_count);
        $this->assertEquals($user->id, $post->likes->first()->user->id);
        $this->assertCount(1, $user->likes);
    }

    public function test_execute_method_dispatch_event()
    {
        Event::fake();

        $post = Post::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $action = new LikePostAction();
        $action->execute($post);

        Event::assertDispatched(LikedPostEvent::class);
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        $post = Post::factory()->create();

        $action = new LikePostAction();

        $catchException = false;

        try {
            $action->execute($post);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }

        $post->refresh();

        $this->assertTrue($catchException);
        $this->assertCount(0, $post->likes);
        $this->assertEquals(0, $post->like_count);
    }

    public function test_execute_method_user_cannot_like_more_than_once()
    {
        $post = Post::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $action = new LikePostAction();
        $action->execute($post);

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


        $post->refresh();

        $this->assertCount(1, $post->likes);
        $this->assertEquals(1, $post->like_count);
        $this->assertEquals($user->id, $post->likes->first()->user->id);
        $this->assertCount(1, $user->likes);

        $this->assertTrue($catchException);
        $this->assertEquals(LikeException::USER_ALREADY_LIKED_POST, $exceptionMessage);
        $this->assertEquals(LikeException::USER_ALREADY_LIKED_POST_STATUS_CODE, $exceptionCode);
    }
}