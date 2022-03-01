<?php

namespace Tests\Feature\Controllers\Api\Profile;

use App\Events\Profile\FollowedUserEvent;
use App\Events\Profile\RequestedToFollowUserEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class FollowUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_method_when_privacy_is_public()
    {
        Event::fake();

        $me = User::factory()->user()->create();
        $shouldFollow = User::factory()->publicAccount()->create();

        $this->actingAs($me);

        $this->postJson(route('api.follow', $shouldFollow->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "{$shouldFollow->username} followed successfully.",
                'success' => true,
            ]);

        $me->refresh();
        $shouldFollow->refresh();

        $this->assertCount(1, $me->followings);
        $this->assertCount(1, $shouldFollow->followers);
        $this->assertCount(0, $me->followers);
        $this->assertCount(0, $shouldFollow->followings);

        $this->assertEquals(1, $me->following_count);
        $this->assertEquals(1, $shouldFollow->follower_count);
        $this->assertEquals(0, $me->follower_count);
        $this->assertEquals(0, $shouldFollow->following_count);

        $this->assertEquals($shouldFollow->id, $me->followings->first()->id);
        $this->assertEquals($me->id, $shouldFollow->followers->first()->id);
        Event::assertDispatched(FollowedUserEvent::class);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    public function test_follow_method_when_privacy_is_private()
    {
        Event::fake();

        $me = User::factory()->user()->create();
        $shouldRequest = User::factory()->privateAccount()->create();

        $this->actingAs($me);

        $this->postJson(route('api.follow', $shouldRequest))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Sent follow request to {$shouldRequest->username} successfully.",
                'success' => true,
            ]);

        $me->refresh();
        $shouldRequest->refresh();

        $this->assertCount(0, $me->followings);
        $this->assertCount(0, $shouldRequest->followers);

        $this->assertEquals(0, $me->following_count);
        $this->assertEquals(0, $shouldRequest->follower_count);

        $this->assertEquals($shouldRequest->id, $me->sentFollowRequests->first()->id);
        $this->assertEquals($me->id, $shouldRequest->receivedFollowRequests->first()->id);
        Event::assertNotDispatched(FollowedUserEvent::class);
        Event::assertDispatched(RequestedToFollowUserEvent::class);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }
}