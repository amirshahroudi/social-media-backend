<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/19/2022
 * Time: 6:48 PM
 */

namespace App\Actions\Post;


use Illuminate\Support\Facades\Storage;

class AddPostMediaToStorageAction
{

    public function execute($mediaFile, $mediaName, $user_id, $post_id)
    {
        $path = "/users/{$user_id}/posts/{$post_id}";
        $filePath = Storage::putFileAs($path, $mediaFile, $mediaName);
        return $filePath;
    }
}