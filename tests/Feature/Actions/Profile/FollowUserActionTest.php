<?php

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\FollowUserAction;
use App\Events\Profile\FollowedUserEvent;
use App\Events\Profile\RequestedToFollowUserEvent;
use App\Exceptions\FollowException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class FollowUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method_when_privacy_is_public()
    {
        Event::fake();

        $me = User::factory()->create();
        $shouldFollow = User::factory()->publicAccount()->create();

        $this->actingAs($me);

        $action = new FollowUserAction();
        $action->execute($shouldFollow);

        $me->refresh();
        $shouldFollow->refresh();

        $this->assertEquals(1, $me->following_count);
        $this->assertEquals(0, $me->follower_count);
        $this->assertEquals(1, $shouldFollow->follower_count);
        $this->assertEquals(0, $shouldFollow->following_count);

        $this->assertCount(1, $me->followings);
        $this->assertCount(1, $shouldFollow->followers);
        $this->assertCount(0, $me->followers);
        $this->assertCount(0, $shouldFollow->followings);

        $this->assertEquals($shouldFollow->id, $me->followings->first()->id);
        $this->assertEquals($me->id, $shouldFollow->followers->first()->id);
        Event::assertDispatched(FollowedUserEvent::class);
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        Event::fake();

        $shouldFollow = User::factory()->create();

        $action = new FollowUserAction();

        $catchException = false;

        try {
            $action->execute($shouldFollow);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }

        $shouldFollow->refresh();

        $this->assertTrue($catchException);
        $this->assertEmpty($shouldFollow->followers);
        Event::assertNotDispatched(FollowedUserEvent::class);
    }

    public function test_execute_method_when_user_followed_user_before()
    {
        Event::fake();

        $me = User::factory()->create();
        $shouldFollow = User::factory()->publicAccount()->create();

        $this->actingAs($me);

        $action = new FollowUserAction();
        $action->execute($shouldFollow);

        $catchException = false;
        $exceptionMessage = '';
        $exceptionCode = '';

        try {
            $action->execute($shouldFollow);
        } catch (FollowException $exception) {
            $catchException = true;
            $exceptionMessage = $exception->getMessage();
            $exceptionCode = $exception->getCode();
        }

        $me->refresh();
        $shouldFollow->refresh();

        $this->assertTrue($catchException);
        $this->assertEquals(FollowException::FOLLOWED_USER_BEFORE, $exceptionMessage);
        $this->assertEquals(FollowException::FOLLOWED_USER_BEFORE_STATUS_CODE, $exceptionCode);
        $this->assertEquals($shouldFollow->id, $me->followings->first()->id);
        $this->assertEquals($me->id, $shouldFollow->followers->first()->id);
    }

    public function test_execute_method_when_privacy_is_private()
    {
        Event::fake();

        $me = User::factory()->create();
        $shouldRequest = User::factory()->privateAccount()->create();

        $this->actingAs($me);

        $action = new FollowUserAction();
        $action->execute($shouldRequest);

        $me->refresh();
        $shouldRequest->refresh();

        $this->assertEquals(0, $me->following_count);
        $this->assertEquals(0, $me->follower_count);

        $this->assertEquals(0, $shouldRequest->following_count);
        $this->assertEquals(0, $shouldRequest->follower_count);

        $this->assertCount(0, $me->followings);
        $this->assertCount(0, $shouldRequest->followers);

        $this->assertEquals($shouldRequest->id, $me->sentFollowRequests->first()->id);
        $this->assertEquals($me->id, $shouldRequest->receivedFollowRequests->first()->id);

        Event::assertNotDispatched(FollowedUserEvent::class);
        Event::assertDispatched(RequestedToFollowUserEvent::class);
    }
}
