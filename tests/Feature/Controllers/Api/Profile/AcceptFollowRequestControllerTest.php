<?php

namespace Tests\Feature\Controllers\Api\Profile;

use App\Actions\Profile\FollowUserAction;
use App\Events\Profile\AcceptedFollowRequestEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AcceptFollowRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_accept_method()
    {
        Event::fake();

        $me = User::factory()->create();
        $privateUser = User::factory()->privateAccount()->create();

        $this->actingAs($me);

        $this->app->make(FollowUserAction::class)->execute($privateUser);

        $this->actingAs($privateUser);

        $this->postJson(route('api.acceptFollowRequest', $me))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Accepted follow request from {$me->username}.",
                'success' => true,
            ]);

        $me->refresh();
        $privateUser->refresh();

        $this->assertEmpty($me->sentFollowRequests);
        $this->assertEmpty($privateUser->receivedFollowRequests);

        $this->assertCount(1, $me->followings);
        $this->assertCount(1, $privateUser->followers);
        $this->assertCount(0, $me->followers);
        $this->assertCount(0, $privateUser->followings);

        $this->assertEquals(1, $me->following_count);
        $this->assertEquals(1, $privateUser->follower_count);
        $this->assertEquals(0, $me->follower_count);
        $this->assertEquals(0, $privateUser->following_count);

        $this->assertEquals($privateUser->id, $me->followings->first()->id);
        $this->assertEquals($me->id, $privateUser->followers->first()->id);
        Event::assertDispatched(AcceptedFollowRequestEvent::class);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }
}