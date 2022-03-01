<?php

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\FollowUserAction;
use App\Actions\Profile\UnfollowUserAction;
use App\Events\Profile\UnfollowedUserEvent;
use App\Exceptions\FollowException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UnfollowUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        //todo check this test again
        Event::fake();

        $me = User::factory()->create();
        $shouldUnfollow = User::factory()->publicAccount()->create();

        $this->actingAs(User::factory()->create());
        $this->app->make(FollowUserAction::class)->execute($shouldUnfollow);

        $this->actingAs(User::factory()->create());
        $this->app->make(FollowUserAction::class)->execute($shouldUnfollow);

        $this->actingAs(User::factory()->create());
        $this->app->make(FollowUserAction::class)->execute($shouldUnfollow);

        $this->actingAs($me);

        $this->app->make(FollowUserAction::class)->execute($shouldUnfollow);
        $this->app->make(FollowUserAction::class)->execute(User::factory()->publicAccount()->create());
        $this->app->make(FollowUserAction::class)->execute(User::factory()->publicAccount()->create());
        $this->app->make(FollowUserAction::class)->execute(User::factory()->publicAccount()->create());

        $action = new UnfollowUserAction();
        $action->execute($shouldUnfollow);

        $me->refresh();

        $shouldUnfollow->refresh();

        $this->assertEmpty($me->followings()->where('user_id', $shouldUnfollow->id)->first());
        $this->assertCount(3, $me->followings);
        $this->assertEquals(3, $me->following_count);

        $this->assertEmpty($shouldUnfollow->followers()->where('follower_id', $me->id)->first());
        $this->assertCount(3, $shouldUnfollow->followers);
        $this->assertEquals(3, $shouldUnfollow->follower_count);

        Event::assertDispatched(UnfollowedUserEvent::class);
    }

    public function test_execute_method_when_not_logged_in()
    {
        Event::fake();

        $shouldUnfollow = User::factory()->create();

        $action = new UnfollowUserAction();

        $catchException = false;

        try {
            $action->execute($shouldUnfollow);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }

        $this->assertTrue($catchException);
        Event::assertNotDispatched(UnfollowedUserEvent::class);
    }

    public function test_execute_method_when_not_followed()
    {
        Event::fake();

        $me = User::factory()->create();
        $shouldUnfollow = User::factory()->create();

        $this->actingAs($me);

        $action = new UnfollowUserAction();

        $catchException = false;
        $exceptionMessage = '';
        $exceptionCode = '';

        try {
            $action->execute($shouldUnfollow);
        } catch (FollowException $exception) {
            $catchException = true;
            $exceptionMessage = $exception->getMessage();
            $exceptionCode = $exception->getCode();
        }

        $me->refresh();
        $shouldUnfollow->refresh();

        $this->assertTrue($catchException);
        $this->assertEquals(FollowException::DIDNT_FOLLOWED_USER_BEFORE, $exceptionMessage);
        $this->assertEquals(FollowException::DIDNT_FOLLOWED_USER_BEFORE_STATUS_CODE, $exceptionCode);
        $this->assertEmpty($me->followings);
        $this->assertEmpty($shouldUnfollow->followers);
        $this->assertEquals(0, $me->following_count);
        $this->assertEquals(0, $shouldUnfollow->follower_count);
        Event::assertNotDispatched(UnfollowedUserEvent::class);
    }
}