<?php

namespace Tests\Feature\Controllers\Api\Profile;

use App\Actions\Profile\FollowUserAction;
use App\Events\Profile\RejectedFollowRequestEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RejectFollowRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_reject_method()
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

        $this->postJson(route('api.rejectFollowRequest', $me))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Rejected follow request from {$me->username}.",
                'success' => true,
            ]);

        $me->refresh();
        $privateAccount->refresh();

        $this->assertEmpty($me->sentFollowRequests()->where('request_to', $privateAccount->id)->first());
        $this->assertEmpty($privateAccount->receivedFollowRequests()->where('request_from', $me->id)->first());

        $this->assertCount(0, $me->followings);
        $this->assertCount(0, $privateAccount->followers);

        $this->assertEquals(0, $me->following_count);
        $this->assertEquals(0, $privateAccount->follower_count);

        Event::assertDispatched(RejectedFollowRequestEvent::class);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }
}