<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/19/2022
 * Time: 8:08 PM
 */

namespace App\Actions\Post;


use App\Events\Post\CreatedPostEvent;
use Illuminate\Auth\AuthenticationException;

class CreatePostAction
{
    /**
     * @var AddPostMediaToStorageAction
     */
    private $addPostMediaToStorageAction;

    public function __construct(AddPostMediaToStorageAction $addPostMediaToStorageAction)
    {
        $this->addPostMediaToStorageAction = $addPostMediaToStorageAction;
    }

    /**
     * @param $caption
     * @param $medias
     * @throws AuthenticationException
     */
    public function execute($caption, $medias)
    {
        //todo post can has null caption
        if (!auth()->check()) {
            throw new AuthenticationException();
        }
        $post = auth()->user()->posts()->create([
            'caption' => $caption,
        ]);
        $i = 0;
        foreach ($medias as $file) {
            $url = $this->addPostMediaToStorageAction->execute(
                $file,
                $file->name,
                auth()->id(),
                $post->id
            );
            $post->postMedias()->create([
                'media_url' => $url,
                'priority'  => $i,
            ]);
            ++$i;
        }

        event(new CreatedPostEvent(auth()->user(), $post));
    }
}