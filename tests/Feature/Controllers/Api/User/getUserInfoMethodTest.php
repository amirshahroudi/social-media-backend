<?php

namespace Tests\Feature\Controllers\Api\User;

use App\Actions\Profile\FollowUserAction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class getUserInfoMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_getUserInfo_method()
    {
        $followerCount = rand(20, 30);
        $followingCount = rand(20, 30);
        $postCount = rand(20, 30);

        $owner = User::factory()
            ->has(Post::factory()->count($postCount))
            ->user()
            ->publicAccount()
            ->create();

        for ($i = 0; $i < $followerCount; $i++) {
            $user = User::factory()->user()->create();
            $this->actingAs($user);
            $this->app->make(FollowUserAction::class)->execute($owner);
        }

        $this->actingAs($owner);
        for ($i = 0; $i < $followingCount; $i++) {
            $user = User::factory()->user()->publicAccount()->create();
            $this->app->make(FollowUserAction::class)->execute($user);
        }
        $owner->update(['privacy' => User::PRIVATE_ACCOUNT]);

        $owner->refresh();

        $requester = User::factory()->user()->create();

        $this->actingAs($requester)
            ->getJson(route('api.users.info', $owner->username))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data'    => [
                    'bio'             => $owner->bio,
                    'post_count'      => $postCount,
                    'follower_count'  => $followerCount,
                    'following_count' => $followingCount,
                ],
                'success' => true,
            ]);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }
}
