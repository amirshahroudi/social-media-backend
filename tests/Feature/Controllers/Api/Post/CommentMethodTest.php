<?php

namespace Tests\Feature\Controllers\Api\Post;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class CommentMethodTest extends TestCase
{
    use RefreshDatabase, JsonRequestForValidation, CreatePost;

    /*
     *  test_execute_method_dispatch_even
     *  test_execute_method_when_user_not_logged_in
     *
     */

    private function can(User $postOwner, User $sender)
    {
        $post = $this->createPost($postOwner, 1);

        $comment = Comment::factory()
            ->for($sender)
            ->for($post)
            ->make()
            ->toArray();
        $text = $comment['text'];

        $this->actingAs($sender);

        $this->postJson(route('api.posts.comment', $post), ['text' => $text])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Comment inserted successfully.",
                'success' => true,
            ]);

        $post->refresh();
        $sender->refresh();

        $this->assertDatabaseHas('comments', $comment);
        $this->assertCount(1, $post->comments);
        $this->assertEquals($comment['text'], $post->comments->first()->text);
        $this->assertEquals($sender->id, $post->comments->first()->user->id);
        $this->assertEquals($sender->id, Comment::where($comment)->first()->user->id);
        $this->assertEquals(1, $post->comment_count);

        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    private function cannot(User $postOwner, User $sender)
    {
        $post = $this->createPost($postOwner, 1);
        
        $text = Comment::factory()->make()->toArray()['text'];

        $this->actingAs($sender);

        $this->postJson(route('api.posts.comment', $post), ['text' => $text])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }

    public function test_comment_method()
    {
        $this->can(User::factory()->publicAccount()->create(), User::factory()->create());
    }

    public function test_comment_validation_required_data()
    {
        $post = Post::factory()->create();

        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [];
        $errors = [
            'text' => 'The text field is required.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.posts.comment', $post), $data, $errors);
    }

    public function test_comment_validation_text_has_string_rule()
    {
        $post = Post::factory()->create();

        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'text' => 123,
        ];
        $errors = [
            'text' => 'The text must be a string.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.posts.comment', $post), $data, $errors);
    }

    public function test_comment_validation_text_has_max_511_rule()
    {
        $post = Post::factory()->create();

        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'text' => Str::random(512),
        ];
        $errors = [
            'text' => 'The text must not be greater than 511 characters.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.posts.comment', $post), $data, $errors);
    }

    public function test_if_post_for_private_user_and_not_followed_cannot_send_comment()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $this->cannot($postOwner, User::factory()->user()->create());
    }

    public function test_private_user_can_send_comment_on_own_post()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();
        $this->can($postOwner, $postOwner);
    }

    public function test_user_can_sent_comment_on_private_user_post_he_has_followed()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $postOwner->id));

        $this->actingAs($postOwner)
            ->postJson(route('api.acceptFollowRequest', $me->id));

        $this->can($postOwner, $me);
    }

    public function test_user_cannot_send_comment_on_private_user_post_he_has_requested()
    {
        $postOwner = User::factory()->user()->privateAccount()->create();

        $this->createPost($postOwner, 1);

        $postOwner->refresh();

        $this->actingAs($me = User::factory()->user()->create())
            ->postJson(route('api.follow', $postOwner->id));

//        $this->actingAs($postOwner)
//            ->postJson(route('api.acceptFollowRequest', $me->id));

        $this->cannot($postOwner, $me);
    }

    public function test_admin_has_access()
    {
        $this->can(
            User::factory()->user()->privateAccount()->create(),
            User::factory()->admin()->create()
        );
    }
}
