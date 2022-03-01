<?php

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostMediaTest extends TestCase
{
    use RefreshDatabase, ModelTestingHelper;

    protected function model(): Model
    {
        return new PostMedia();
    }

    public function test_postMedia_relation_with_post()
    {
        $postMedia = PostMedia::factory()
            ->for($post = Post::factory()->create())
            ->create();

        $this->assertCount(1, $post->postMedias);
        $this->assertTrue(isset($postMedia->post_id));
        $this->assertTrue($postMedia->post instanceof Post);
        $this->assertEquals($postMedia->post_id, $postMedia->post->id);
        $this->assertEquals($postMedia->post->id, $post->id);
    }
}
