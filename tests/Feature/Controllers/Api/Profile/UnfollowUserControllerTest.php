<?php

namespace Tests\Feature\Controllers\Api\Profile;

use App\Actions\Profile\FollowUserAction;
use App\Events\Profile\UnfollowedUserEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UnfollowUserControllerTest extends TestCase
{
    use  RefreshDatabase;

    public function test_unfollow_method()
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

        $this->postJson(route('api.unfollow', $shouldUnfollow))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Unfollowed {$shouldUnfollow->username}.",
                'success' => true,
            ]);

        $me->refresh();

        $shouldUnfollow->refresh();

        $this->assertEmpty($me->followings()->where('user_id', $shouldUnfollow->id)->first());
        $this->assertCount(3, $me->followings);
        $this->assertEquals(3, $me->following_count);

        $this->assertEmpty($shouldUnfollow->followers()->where('follower_id', $me->id)->first());
        $this->assertCount(3, $shouldUnfollow->followers);
        $this->assertEquals(3, $shouldUnfollow->follower_count);

        Event::assertDispatched(UnfollowedUserEvent::class);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }
}