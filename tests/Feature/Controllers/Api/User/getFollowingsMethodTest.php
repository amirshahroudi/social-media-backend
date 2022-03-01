<?php

namespace Tests\Feature\Controllers\Api\User;

use App\Actions\Profile\AcceptFollowRequestAction;
use App\Actions\Profile\FollowUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class getFollowingsMethodTest extends TestCase
{
    use RefreshDatabase;

    private function can(User $owner, User $requester, $followingCount)
    {
        $followings = $owner->followings()->latest('user_user.created_at')->get(['id', 'username'])->toArray();

        $shouldSee = $followingCount > 9 ? $followings[rand(0, 9)] : $followings[rand(0, $followingCount - 2)];
//        unset($shouldSee['user_id']);
        unset($shouldSee['pivot']);

        $this->actingAs($requester);

        $this->getJson(route('api.users.followings', $owner->username))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data'  => [
                    '*' => ['id', 'username'],
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta'  => [
                    'current_page',
                    'from',
                    'last_page', 'links',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ])
            ->assertSee($shouldSee, false)
            ->assertSee(['total' => $followingCount])
            ->assertSee(['per_page' => 10]);

        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(User $owner, User $requester)
    {
        $this->actingAs($requester);

        $this->getJson(route('api.users.followings', $owner->username))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_getFollowings_method()
    {
        $owner = User::factory()
            ->has(User::factory()->user()->count($followingCount = rand(10, 30)), 'followings')
            ->user()
            ->publicAccount()
            ->create();

        $this->can($owner, User::factory()->user()->create(), $followingCount);
    }

    public function test_if_user_is_private_and_not_followed_cannot_see_followings()
    {
        $owner = User::factory()
            ->has(User::factory()->user()->publicAccount()->count(1), 'followings')
            ->user()
            ->privateAccount()
            ->create();

        $this->cannot($owner, User::factory()->user()->create());
    }

    public function test_private_user_can_see_own_followings()
    {
        $owner = User::factory()
            ->privateAccount()
            ->user()
            ->has(User::factory()->user()->count($followingCount = rand(10, 30)), 'followings')
            ->create();

        $this->can($owner, $owner, $followingCount);
    }

    public function test_user_can_see_private_user_followings_he_has_followed()
    {
        $owner = User::factory()
            ->privateAccount()
            ->user()
            ->has(User::factory()->user()->count($followingCount = rand(2, 9)), 'followings')
            ->create();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $owner->id));

        $this->actingAs($owner)
            ->postJson(route('api.acceptFollowRequest', $me->id));

//        $this->actingAs($me = User::factory()->user()->create());
//        $this->app->make(FollowUserAction::class)->execute($owner);
//        $this->actingAs($owner);
//        $this->app->make(AcceptFollowRequestAction::class)->execute($me);

        $me->refresh();
        $owner->refresh();

        ++$followingCount;

        $this->can($owner, $me, $followingCount);
    }

    public function test_user_cannot_see_private_user_followings_he_has_requested()
    {
        $owner = User::factory()
            ->privateAccount()
            ->user()
            ->has(User::factory()->user()->count(rand(10, 30)), 'followings')
            ->create();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $owner->id));

//        $this->actingAs($owner)
//            ->postJson(route('api.acceptFollowRequest', $me->id));

        $me->refresh();
        $owner->refresh();

        $this->cannot($owner, $me);
    }
}
