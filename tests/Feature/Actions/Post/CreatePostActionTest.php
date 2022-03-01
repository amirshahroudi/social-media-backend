<?php

namespace Tests\Feature\Actions\Post;

use App\Actions\Post\CreatePostAction;
use App\Events\Post\CreatedPostEvent;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreatePostActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        $post = Post::factory()
            ->for($user = User::factory()->create())
            ->make()
            ->toArray();

        $caption = $post['caption'];

        $medias = array(UploadedFile::fake()->image('xxx.jpg'),
                        UploadedFile::fake()->image('yyy.jpg'),
                        UploadedFile::fake()->image('zzz.jpg'),
                        UploadedFile::fake()->image('www.jpg'),
                        UploadedFile::fake()->image('aaa.jpg'),
        );

        $this->actingAs($user);

        $action = $this->app->make(CreatePostAction::class);
        $action->execute($caption, $medias);

        $user->refresh();


        $this->assertDatabaseCount('posts', 1);
        $this->assertDatabaseHas('posts', compact('caption'));
        $this->assertEquals($caption, $user->posts->first()->caption);
        $this->assertCount(1, $user->posts);
        $this->assertEquals(1, $user->post_count);
        /*        $this->assertTrue(Storage::exists($user->posts->first()->image_url));*/
        $actualPost = $user->posts->first();
        $actualPost->refresh();
        $this->assertCount(5, $actualPost->postMedias);
        $this->assertTrue(Storage::exists($actualPost->postMedias->first()->media_url));
        //todo can i test priority?
    }

    public function test_execute_method_dispatch_event()
    {
        Event::fake();

        $post = Post::factory()
            ->for($user = User::factory()->create())
            ->make()
            ->toArray();

        $caption = $post['caption'];

        $medias = array(UploadedFile::fake()->image('xxx.jpg'),
                        UploadedFile::fake()->image('yyy.jpg'),
                        UploadedFile::fake()->image('zzz.jpg'),
                        UploadedFile::fake()->image('www.jpg'),
                        UploadedFile::fake()->image('aaa.jpg'),
        );

        $this->actingAs($user);

        $action = $this->app->make(CreatePostAction::class);
        $action->execute($caption, $medias);

        Event::assertDispatched(CreatedPostEvent::class);
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        $post = Post::factory()
            ->for($user = User::factory()->create())
            ->make()
            ->toArray();

        $caption = $post['caption'];

        $medias = array(UploadedFile::fake()->image('xxx.jpg'),
                        UploadedFile::fake()->image('yyy.jpg'),
                        UploadedFile::fake()->image('zzz.jpg'),
                        UploadedFile::fake()->image('www.jpg'),
                        UploadedFile::fake()->image('aaa.jpg'),
        );

        $action = $this->app->make(CreatePostAction::class);

        $catchException = false;

        try {
            $action->execute($caption, $medias);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }

        $user->refresh();

        $this->assertTrue($catchException);
        $this->assertDatabaseCount('posts', 0);
        $this->assertDatabaseMissing('posts', compact('caption'));
        $this->assertCount(0, $user->posts);
        $this->assertEquals(0, $user->post_count);
    }
}
