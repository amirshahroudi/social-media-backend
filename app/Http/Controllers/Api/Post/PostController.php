<?php

namespace App\Http\Controllers\Api\Post;

use App\Actions\Post\CommentPostAction;
use App\Actions\Post\CreatePostAction;
use App\Actions\Post\DestroyPostAction;
use App\Actions\Post\LikePostAction;
use App\Actions\Post\UnlikePostAction;
use App\Actions\Post\UpdatePostAction;
use App\Helper\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Post\CommentRequest;
use App\Http\Requests\Api\Post\StoreRequest;
use App\Http\Requests\Api\Post\UpdateRequest;
use App\Http\Resources\CommentResourceCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    use APIResponseHelper;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @param CreatePostAction $action
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function store(StoreRequest $request, CreatePostAction $action)
    {
        //todo caption can be null
        $action->execute($request->validated()['caption'], $request->file('medias'));

        return
            $this->send_custom_response(
                null,
                'Post created successfully.',
                Response::HTTP_CREATED,
                true
            );
    }

    /**
     * Display the specified resource.
     *
     * @param Post $post
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Post $post)
    {
        $this->authorize('show', $post);

        return
            $this->send_custom_response(
                new PostResource($post),
                null,
                Response::HTTP_OK,
                true
            );
    }

    /**
     * @param Post $post
     * @return CommentResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getComments(Post $post)
    {
        $this->authorize('show', $post);

        return new CommentResourceCollection($post->comments()->latest()->paginate(10));
    }

    /**
     * @param Post $post
     * @param $number
     * @return string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getMedias(Post $post, $number)
    {
        $this->authorize('show', $post);

        $media_url = $post->postMedias()->where('priority', $number)->firstOrFail()->media_url;
        $media = Storage::get($media_url);
        return $media;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param Post $post
     * @param UpdatePostAction $action
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateRequest $request, Post $post, UpdatePostAction $action)
    {
        //todo caption can be null
        $this->authorize('update', $post);

        $action->execute($post, $request->validated()['caption']);

        return
            $this->send_custom_response(
                null,
                'Post updated successfully.',
                Response::HTTP_OK,
                true
            );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Post $post
     * @param DestroyPostAction $action
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Post $post, DestroyPostAction $action)
    {
        $this->authorize('destroy', $post);

        $action->execute($post);

        return
            $this->send_custom_response(
                null,
                'Post destroyed successfully.',
                Response::HTTP_OK,
                true
            );
    }

    public function like(Post $post, LikePostAction $action)
    {
        $this->authorize('like', $post);

        $action->execute($post);

        return
            $this->send_custom_response(
                null,
                'Post liked successfully.',
                Response::HTTP_OK,
                true
            );
    }

    public function unlike(Post $post, UnlikePostAction $action)
    {
        $this->authorize('like', $post);

        $action->execute($post);

        return
            $this->send_custom_response(
                null,
                'Post unliked successfully.',
                Response::HTTP_OK,
                true
            );
    }

    public function insertComment(CommentRequest $request, Post $post, CommentPostAction $action)
    {
        $this->authorize('show', $post);

        $action->execute($post, $request->validated()['text']);

        return
            $this->send_custom_response(
                null,
                'Comment inserted successfully.',
                Response::HTTP_OK,
                true
            );
    }
}
