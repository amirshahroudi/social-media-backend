<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase, ModelTestingHelper;

    protected function model(): Model
    {
        return new Comment();
    }

//    public function test_insert_data()
//    {
//        $data = Comment::factory()->make()->toArray();
//        Comment::create($data);
//        $this->assertDatabaseHas('comments', $data);
//    }

    public function test_comment_relationship_with_user()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()
            ->for($user)
            ->create();

        $this->assertTrue(isset($comment->user_id));
        $this->assertTrue($comment->user instanceof User);
        $this->assertEquals($comment->user->id, $comment->user_id);
        $this->assertEquals($comment->user->id, $user->id);
    }

    public function test_comment_relationship_with_post()
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()
            ->for($post)
            ->create();

        $this->assertCount(1, $post->comments);
        $this->assertTrue(isset($comment->post_id));
        $this->assertTrue($comment->post instanceof Post);
        $this->assertEquals($comment->post_id, $comment->post->id);
        $this->assertEquals($comment->post->id, $post->id);
    }

    public function test_comment_relation_with_like()
    {
        $likeCount = rand(1, 10);
        $comment = Comment::factory()
            ->has(Like::factory()->count($likeCount))
            ->create();

        $this->assertTrue($comment->likes->first() instanceof Like);
        $this->assertCount($likeCount, $comment->likes);
        $this->assertEquals($comment->id, $comment->likes()->first()->likable_id);
        $this->assertEquals(Comment::class, get_class($comment->likes()->first()->likable));
        $this->assertCount(
            $likeCount,
            DB::select(
                'select * from likes where likable_id = :commentId and likable_type = :commentType',
                ['commentId' => $comment->id, 'commentType' => get_class(new Comment())])
        );

    }
}

