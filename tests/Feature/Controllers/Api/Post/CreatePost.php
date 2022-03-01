<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 2/15/2022
 * Time: 5:39 PM
 */

namespace Tests\Feature\Controllers\Api\Post;


use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;

trait CreatePost
{
    private function createPost(User $postOwner, $mediaCount)
    {
        $post = Post::factory()
            ->for($postOwner)
            ->make()
            ->toArray();

        $caption = $post['caption'];

        $medias = array();
        for ($i = 0; $i < $mediaCount; $i++) {
            $medias[] = UploadedFile::fake()->image("media{$i}.jpg");
        }
        $this->actingAs($postOwner);
        $this->postJson(
            route('api.posts.store'),
            [
                'caption' => $caption,
                'medias'  => $medias,
            ]
        );

        $postOwner->refresh();

        $post = $postOwner->posts()->where('caption', $caption)->first();

        return $post;
    }

}