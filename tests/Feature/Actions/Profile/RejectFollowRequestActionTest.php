<?php

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\FollowUserAction;
use App\Actions\Profile\RejectFollowRequestAction;
use App\Events\Profile\RejectedFollowRequestEvent;
use App\Exceptions\FollowException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RejectFollowRequestActionTest extends TestCase
{
    use RefreshDatabase;

    //test execute method
    //test if not logged in
    //test if user already in followers
    //test if have not any request from that user

    public function test_execute_method()
    {
        Event::fake();

        $me = User::factory()->create();
        $privateAccount = User::factory()->privateAccount()->create();

        $this->actingAs($me);

        $this->app->make(FollowUserAction::class)->execute($privateAccount);
        $this->app->make(FollowUserAction::class)->execute(User::factory()->privateAccount()->create());
        $this->app->make(FollowUserAction::class)->execute(User::factory()->privateAccount()->create());

        $this->actingAs(User::factory()->create());

        $this->app->make(FollowUserAction::class)->execute($privateAccount);

        $this->actingAs($privateAccount);

        $action = new RejectFollowRequestAction();
        $action->execute($me);

        $me->refresh();
        $privateAccount->refresh();

        $this->assertEmpty($me->sentFollowRequests()->where('request_to', $privateAccount->id)->first());
        $this->assertEmpty($privateAccount->receivedFollowRequests()->where('request_from', $me->id)->first());
        $this->assertCount(0, $me->followings);
        $this->assertCount(0, $privateAccount->followers);

        $this->assertEquals(0, $me->following_count);
        $this->assertEquals(0, $privateAccount->follower_count);

        Event::assertDispatched(RejectedFollowRequestEvent::class);
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        Event::fake();

        $me = User::factory()->create();
        $privateAccount = User::factory()->privateAccount()->create();

        $action = new RejectFollowRequestAction();

        $catchException = false;
        try {
            $action->execute($me);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }
        $me->refresh();
        $privateAccount->refresh();

        $this->assertTrue($catchException);
        Event::assertNotDispatched(RejectedFollowRequestEvent::class);
    }

    /*public function test_execute_method_when_user_already_in_followers()
    {
        //it cannot occur
    }*/

    public function test_execute_method_when_user_have_not_request_from_this_user()
    {
        Event::fake();

        $me = User::factory()->create();
        $privateAccount = User::factory()->privateAccount()->create();

        $this->actingAs($me);

        $this->app->make(FollowUserAction::class)->execute(User::factory()->privateAccount()->create());
        $this->app->make(FollowUserAction::class)->execute(User::factory()->privateAccount()->create());

        $this->actingAs(User::factory()->create());

        $this->app->make(FollowUserAction::class)->execute($privateAccount);

        $this->actingAs($privateAccount);

        $action = new RejectFollowRequestAction();

        $catchException = false;
        $exceptionMessage = '';
        $exceptionCode = '';

        try {
            $action->execute($me);
        } catch (FollowException $exception) {
            $catchException = true;
            $exceptionMessage = $exception->getMessage();
            $exceptionCode = $exception->getCode();
        }

        $me->refresh();
        $privateAccount->refresh();

        $this->assertTrue($catchException);
        $this->assertEquals(FollowException::DIDNT_HAVE_FOLLOW_REQUEST_FROM_THIS_USER_TO_REJECT,
            $exceptionMessage);
        $this->assertEquals(FollowException::DIDNT_HAVE_FOLLOW_REQUEST_FROM_THIS_USER_TO_REJECT_STATUS_CODE,
            $exceptionCode);
        $this->assertCount(0, $me->followings);
        $this->assertCount(0, $privateAccount->followers);

        $this->assertEquals(0, $me->following_count);
        $this->assertEquals(0, $privateAccount->follower_count);

        Event::assertNotDispatched(RejectedFollowRequestEvent::class);
    }
}
