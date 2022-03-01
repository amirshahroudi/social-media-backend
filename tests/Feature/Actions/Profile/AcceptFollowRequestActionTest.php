<?php

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\AcceptFollowRequestAction;
use App\Actions\Profile\FollowUserAction;

use App\Events\Profile\AcceptedFollowRequestEvent;
use App\Exceptions\FollowException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AcceptFollowRequestActionTest extends TestCase
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
        $privateUser = User::factory()->privateAccount()->create();

        $this->actingAs($me);

        $this->app->make(FollowUserAction::class)->execute($privateUser);

        $this->actingAs($privateUser);

        $action = new AcceptFollowRequestAction();
        $action->execute($me);

        $me->refresh();
        $privateUser->refresh();

        $this->assertEmpty($me->sentFollowRequests);
        $this->assertEmpty($privateUser->receivedFollowRequests);

        $this->assertCount(1, $me->followings);
        $this->assertCount(1, $privateUser->followers);

        $this->assertEquals(1, $me->following_count);
        $this->assertEquals(0, $me->follower_count);
        $this->assertEquals(1, $privateUser->follower_count);
        $this->assertEquals(0, $privateUser->following_count);

        $this->assertCount(0, $me->followers);
        $this->assertCount(0, $privateUser->followings);

        $this->assertEquals($privateUser->id, $me->followings->first()->id);
        $this->assertEquals($me->id, $privateUser->followers->first()->id);
        Event::assertDispatched(AcceptedFollowRequestEvent::class);
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        Event::fake();

        $me = User::factory()->create();
        $privateUser = User::factory()->privateAccount()->create();

        $action = new AcceptFollowRequestAction();

        $catchException = false;

        try {
            $action->execute($me);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }


        $me->refresh();
        $privateUser->refresh();

        $this->assertTrue($catchException);

        $this->assertCount(0, $me->followings);
        $this->assertEquals(0, $me->following_count);

        $this->assertCount(0, $privateUser->followers);
        $this->assertEquals(0, $privateUser->follower_count);

        $this->assertCount(0, $me->followers);
        $this->assertCount(0, $privateUser->followings);

        Event::assertNotDispatched(AcceptedFollowRequestEvent::class);
    }

    /*public function test_execute_method_when_user_already_in_followers()
    {
        perhaps it cannot occur
    }
    */

    public function test_execute_method_when_user_have_not_request_from_this_user()
    {
        Event::fake();

        $me = User::factory()->create();
        $privateUser = User::factory()->privateAccount()->create();

        $this->actingAs($privateUser);

        $action = new AcceptFollowRequestAction();

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
        $privateUser->refresh();

        $this->assertTrue($catchException);
        $this->assertEquals(FollowException::DIDNT_HAVE_FOLLOW_REQUEST_FROM_THIS_USER_TO_ACCEPT,
            $exceptionMessage);
        $this->assertEquals(FollowException::DIDNT_HAVE_FOLLOW_REQUEST_FROM_THIS_USER_TO_ACCEPT_STATUS_CODE, $exceptionCode);

        $this->assertCount(0, $me->followings);
        $this->assertEquals(0, $me->following_count);

        $this->assertCount(0, $privateUser->followers);
        $this->assertEquals(0, $privateUser->follower_count);

        $this->assertCount(0, $me->followers);
        $this->assertEquals(0, $me->follower_count);

        $this->assertCount(0, $privateUser->followings);
        $this->assertEquals(0, $privateUser->following_count);

        Event::assertNotDispatched(AcceptedFollowRequestEvent::class);
    }
}
