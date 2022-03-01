<?php

namespace Tests\Feature\Controllers\Api\Post;

use App\Events\Post\CreatedPostEvent;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class StoreMethodTest extends TestCase
{
    use RefreshDatabase, JsonRequestForValidation;

    public function test_store_method()
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

        $this->postJson(route('api.posts.store'), ['caption' => $caption, 'medias' => $medias])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'message' => "Post created successfully.",
                'success' => true,
            ]);

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
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
        //todo can i test priority?
    }

    public function test_store_method_dispatch_event()
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

        $this->postJson(route('api.posts.store'), ['caption' => $caption, 'medias' => $medias]);

        Event::assertDispatched(CreatedPostEvent::class);
    }

    public function test_store_validation_required_data()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [];
        $errors = [
            'caption' => 'The caption field is required.',
            'medias'  => 'The medias field is required.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.posts.store'), $data, $errors);
    }

    public function test_store_validation_caption_has_string_rule()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'caption' => 123,
        ];
        $errors = [
            'caption' => 'The caption must be a string.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.posts.store'), $data, $errors);
    }

    public function test_store_validation_caption_has_max_2047_rule()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'caption' => Str::random(2048),
        ];
        $errors = [
            'caption' => 'The caption must not be greater than 2047 characters.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.posts.store'), $data, $errors);
    }

    public function test_store_validation_medias_has_array_rule()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'medias' => UploadedFile::fake()->image('this.jpg'),
        ];
        $errors = [
            'medias' => 'The medias must be an array.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.posts.store'), $data, $errors);
    }

    public function test_store_validation_medias_has_max_10_rule()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'medias' => array(UploadedFile::fake()->image('this1.jpg'),
                              UploadedFile::fake()->image('this2.jpg'),
                              UploadedFile::fake()->image('this3.jpg'),
                              UploadedFile::fake()->image('this4.jpg'),
                              UploadedFile::fake()->image('this5.jpg'),
                              UploadedFile::fake()->image('this6.jpg'),
                              UploadedFile::fake()->image('this7.jpg'),
                              UploadedFile::fake()->image('this8.jpg'),
                              UploadedFile::fake()->image('this9.jpg'),
                              UploadedFile::fake()->image('this10.jpg'),
                              UploadedFile::fake()->image('this11.jpg'),
            ),
        ];
        $errors = [
            'medias' => 'The medias must not have more than 10 items.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.posts.store'), $data, $errors);
    }

    public function test_store_validation_medias_has_file_rule()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'medias' => array(1, 2, 3, 4),
        ];
        $errors = [
            'medias.0' => 'The medias.0 must be a file.',
            'medias.1' => 'The medias.1 must be a file.',
            'medias.2' => 'The medias.2 must be a file.',
            'medias.3' => 'The medias.3 must be a file.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.posts.store'), $data, $errors);
    }

    public function test_store_validation_medias_has_mime_rule()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'medias' => array(UploadedFile::fake()->image('this1.mp3'),
                              UploadedFile::fake()->image('this2.gif'),
                              UploadedFile::fake()->image('this3.mp4'),
            ),
        ];
        $errors = [
            'medias.0' => 'The medias.0 must be a file of type: jpeg, jpg',
            'medias.1' => 'The medias.1 must be a file of type: jpeg, jpg',
            'medias.2' => 'The medias.2 must be a file of type: jpeg, jpg',
        ];
        $this->sendPostJsonRequestForValidation(route('api.posts.store'), $data, $errors);
    }

    public function test_store_validation_each_medias_file_max_1023kb()
    {
        $user = User::factory()->user()->create();
        $this->actingAs($user);

        $data = [
            'medias' => array(UploadedFile::fake()->create('one.jpg', 1024),
                              UploadedFile::fake()->create('two.jpg', 1024),
                              UploadedFile::fake()->create('three.jpg', 1024),
            ),
        ];
        $errors = [
            'medias.0' => 'The medias.0 must not be greater than 1023 kilobytes.',
            'medias.1' => 'The medias.1 must not be greater than 1023 kilobytes.',
            'medias.2' => 'The medias.2 must not be greater than 1023 kilobytes.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.posts.store'), $data, $errors);
    }
}