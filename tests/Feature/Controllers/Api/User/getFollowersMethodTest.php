<?php

namespace Tests\Feature\Controllers\Api\User;

use App\Actions\Profile\FollowUserAction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class getFollowersMethodTest extends TestCase
{
    use RefreshDatabase;

    private function can(User $owner, User $requester, $followerCount)
    {
        $followers = $owner->followers()->latest('user_user.created_at')->get(['id', 'username'])->toArray();

        $shouldSee = $followers[rand(0, 9)];
//        unset($shouldSee['user_id']);
        unset($shouldSee['pivot']);

        $this->actingAs($requester);

        $this->getJson(route('api.users.followers', $owner->username))
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
            ->assertSee(['total' => $followerCount])
            ->assertSee(['per_page' => 10]);

        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(User $owner, User $requester)
    {
        $this->actingAs($requester);

        $this->getJson(route('api.users.followers', $owner->username))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_getFollowers_method()
    {
        $owner = User::factory()
            ->publicAccount()
            ->user()
            ->has(User::factory()->user()->count($followerCount = rand(10, 30)), 'followers')
            ->create();

//        $followers = $owner->followers()->latest('user_user.created_at')->get(['id', 'user_id', 'username'])->toArray();
        $this->can($owner, User::factory()->user()->create(), $followerCount);
    }

    public function test_if_user_is_private_and_not_followed_cannot_see_followers()
    {
        $owner = User::factory()
            ->has(User::factory()->user()->publicAccount()->count(1), 'followers')
            ->user()
            ->privateAccount()
            ->create();

        $this->cannot($owner, User::factory()->user()->create());
    }

    public function test_private_user_can_see_own_followers()
    {
        $owner = User::factory()
            ->privateAccount()
            ->user()
            ->has(User::factory()->user()->count($followerCount = rand(10, 30)), 'followers')
            ->create();

        $this->can($owner, $owner, $followerCount);
    }

    public function test_user_can_see_private_user_followers_he_has_followed()
    {
        $owner = User::factory()
            ->privateAccount()
            ->user()
            ->has(User::factory()->user()->count($followerCount = rand(10, 30)), 'followers')
            ->create();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $owner->id));

        $this->actingAs($owner)
            ->postJson(route('api.acceptFollowRequest', $me->id));

        $me->refresh();
        $owner->refresh();

        $followerCount++;
        $this->can($owner, $me, $followerCount);
    }

    public function test_user_cannot_see_private_user_followers_he_has_requested()
    {
        $owner = User::factory()
            ->privateAccount()
            ->user()
            ->has(User::factory()->user()->count(rand(10, 30)), 'followers')
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
