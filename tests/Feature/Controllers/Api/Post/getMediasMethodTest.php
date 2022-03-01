<?php

namespace Tests\Feature\Controllers\Api\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class getMediasMethodTest extends TestCase
{
    use RefreshDatabase, CreatePost;

    private function can(User $postOwner, User $requester)
    {
        $number = 0;
        $mediaCount = rand(1, 10);

        $post = $this->createPost($postOwner, $mediaCount);

        $this->actingAs($requester);

        $response = $this->getJson(route('api.posts.medias', ['post' => $post->id, 'number' => $number]))
            ->assertStatus(Response::HTTP_OK);

        $media_url = $post->postMedias()->where('priority', $number)->firstOrFail()->media_url;
        $media = Storage::get($media_url);
        $this->assertEquals($media, $response->getContent());
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(User $postOwner, User $requester)
    {
        $number = 0;
        $mediaCount = rand(1, 10);

        $post = $this->createPost($postOwner, $mediaCount);

        $this->actingAs($requester);

        $this->getJson(route('api.posts.medias', ['post' => $post->id, 'number' => $number]))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_getMedias_method()
    {
        $this->can(User::factory()->publicAccount()->create(), User::factory()->user()->create());
    }

    public function test_if_post_for_private_user_and_not_followed_cannot_see_its_medias()
    {
        $this->cannot(User::factory()->privateAccount()->create(), User::factory()->user()->create());
    }

    public function test_private_user_can_see_own_post()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $this->can($postOwner, $postOwner);
    }

    public function test_user_can_see_private_user_post_he_has_followed()
    {
        $postOwner = User::factory()->privateAccount()->create();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $postOwner->id));

        $this->actingAs($postOwner)
            ->postJson(route('api.acceptFollowRequest', $me->id));

        $me->refresh();
        $postOwner->refresh();

        $this->can($postOwner, $me);
    }

    public function test_user_cannot_see_private_user_post_he_has_requested()
    {
        $postOwner = User::factory()->privateAccount()->create();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $postOwner->id));

//        $this->actingAs($postOwner)
//            ->postJson(route('api.acceptFollowRequest', $me->id));

        $me->refresh();
        $postOwner->refresh();

        $this->cannot($postOwner, $me);
    }
}
