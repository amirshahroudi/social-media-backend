<?php

namespace Tests\Feature\Controllers\Api\User;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\Feature\Controllers\Api\Post\CreatePost;
use Tests\TestCase;

class getPostsMethodTest extends TestCase
{
    use RefreshDatabase, CreatePost;

    private function can(User $owner, User $requester, $postCount)
    {
        $posts = $owner->posts()
            ->latest()
            ->get(['id', 'caption', 'like_count', 'comment_count', 'created_at',])
            ->toArray();

        $shouldSee = $postCount > 9 ? $posts[rand(0, 9)] : $posts[rand(0, $postCount - 2)];
        unset($shouldSee['created_at']);

        $this->actingAs($requester);

        $this->getJson(route('api.users.posts', $owner->username))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data'  => [
                    '*' => ['id', 'username', 'caption', 'medias_count', 'like_count', 'comment_count', 'created_at'],
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
            ->assertSee(['total' => $postCount])
            ->assertSee(['per_page' => 10]);

        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(User $owner, User $requester)
    {
        $this->actingAs($requester);

        $this->getJson(route('api.users.posts', $owner->username))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_getPosts_method()
    {
        $owner = User::factory()->user()->publicAccount()->create();
        $postCount = rand(10, 20);
        for ($i = 0; $i < $postCount; $i++) {
            $this->createPost($owner, rand(1, 3));
        }

        $this->can($owner, User::factory()->user()->create(), $postCount);
    }

    public function test_if_user_is_private_and_not_followed_cannot_see_posts()
    {
        $owner = User::factory()->user()->privateAccount()->create();
        $postCount = rand(10, 20);
        for ($i = 0; $i < $postCount; $i++) {
            $this->createPost($owner, rand(1, 3));
        }

        $this->cannot($owner, User::factory()->user()->create());
    }

    public function test_private_user_can_see_own_posts()
    {
        $owner = User::factory()->user()->privateAccount()->create();
        $postCount = rand(10, 20);
        for ($i = 0; $i < $postCount; $i++) {
            $this->createPost($owner, rand(1, 3));
        }

        $this->can($owner, $owner, $postCount);
    }

    public function test_user_can_see_private_user_posts_he_has_followed()
    {
        $owner = User::factory()->user()->privateAccount()->create();
        $postCount = rand(10, 20);
        for ($i = 0; $i < $postCount; $i++) {
            $this->createPost($owner, rand(1, 3));
        }

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $owner->id));

        $this->actingAs($owner)
            ->postJson(route('api.acceptFollowRequest', $me->id));

        $me->refresh();
        $owner->refresh();

        $this->can($owner, $me, $postCount);
    }

    public function test_user_cannot_see_private_user_posts_he_has_requested()
    {
        $owner = User::factory()->user()->privateAccount()->create();
        $postCount = rand(10, 20);
        for ($i = 0; $i < $postCount; $i++) {
            $this->createPost($owner, rand(1, 3));
        }

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $owner->id));

//        $this->actingAs($owner)
//            ->postJson(route('api.acceptFollowRequest', $me->id));

        $me->refresh();
        $owner->refresh();

        $this->cannot($owner, $me);
    }

}
