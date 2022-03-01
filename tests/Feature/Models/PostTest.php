<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, ModelTestingHelper;

    protected function model(): Model
    {
        return new Post();
    }

    public function test_post_relationship_with_user()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user)
            ->create();

        $this->assertTrue(isset($post->user_id));
        $this->assertTrue($post->user instanceof User);
        $this->assertEquals($post->user->id, $post->user_id);
        $this->assertEquals($post->user->id, $user->id);
    }

    public function test_post_relation_with_comment()
    {
        $commentCount = rand(1, 10);
        $post = Post::factory()
            ->has(Comment::factory()->count($commentCount))
            ->create();

        $this->assertCount($commentCount, $post->comments);
        $this->assertTrue($post->comments->first() instanceof Comment);
        $this->assertCount($commentCount,
            DB::select('select * from comments where post_id = :postId', ['postId' => $post->id]));
    }

    public function test_post_relation_with_like()
    {
        $likeCount = rand(1, 10);
        $post = Post::factory()
            ->has(Like::factory()->count($likeCount))
            ->create();

        $this->assertCount($likeCount, $post->likes);
        $this->assertEquals($post->id, $post->likes->first()->likable_id);
        $this->assertTrue($post->likes->first() instanceof Like);
        $this->assertCount(
            $likeCount,
            DB::select(
                'select * from likes where likable_id = :postId and likable_type = :postType',
                ['postId' => $post->id, 'postType' => get_class(new Post())])
        );
    }

    public function test_post_relation_with_postMedia()
    {
        $postMediaCount = rand(1, 10);

        $post = Post::factory()
            ->has(PostMedia::factory()->count($postMediaCount), 'postMedias')
            ->create();

        $this->assertCount($postMediaCount, $post->postMedias);
        $this->assertTrue($post->postMedias->first() instanceof PostMedia);
        $this->assertCount($postMediaCount,
            DB::select('select * from postMedias where post_id = :postId', ['postId' => $post->id]));
    }
}
