<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 2/8/2022
 * Time: 10:43 PM
 */

use App\Http\Controllers\Api\Post\PostController;
use Illuminate\Support\Facades\Route;

Route::apiResource('posts', PostController::class)
    ->middleware(['auth:sanctum']);

Route::post('posts/{post}/like', [PostController::class, 'like'])
    ->name('posts.like')
    ->middleware(['auth:sanctum']);

Route::post('posts/{post}/unlike', [PostController::class, 'unlike'])
    ->name('posts.unlike')
    ->middleware(['auth:sanctum']);

Route::post('posts/{post}/comment', [PostController::class, 'insertComment'])
    ->name('posts.comment')
    ->middleware(['auth:sanctum']);

Route::get('posts/{post}/comments', [PostController::class, 'getComments'])
    ->name('posts.comments')
    ->middleware(['auth:sanctum']);

Route::get('posts/{post}/{number}', [PostController::class, 'getMedias'])
    ->name('posts.medias')
    ->middleware(['auth:sanctum']);
