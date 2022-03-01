<?php

namespace Tests\Feature\Actions\Post;

use App\Actions\Post\UpdatePostAction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdatePostActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        $post = Post::factory()
            ->for($user = User::factory()->create())
            ->create();

        $oldPost = $post->toArray();
        unset($oldPost['created_at']);
        unset($oldPost['updated_at']);

        $data = Post::factory()->make()->toArray();
        $newCaption = $data['caption'];

        $this->actingAs($user);

        $action = new UpdatePostAction();
        $action->execute($post, $newCaption);

        $post->refresh();

        $this->assertEquals($newCaption, $post->caption);
        $this->assertDatabaseMissing('posts', $oldPost);
        $this->assertDatabaseCount('posts', 1);
        $this->assertDatabaseHas('posts', ['caption' => $newCaption]);
    }
}
