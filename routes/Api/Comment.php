<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 2/9/2022
 * Time: 11:05 PM
 */

use App\Http\Controllers\Api\Comment\CommentController;
use Illuminate\Support\Facades\Route;

Route::delete('comments/{comment}', [CommentController::class, 'destroy'])
    ->name('comments.destroy')
    ->middleware(['auth:sanctum']);

Route::post('comments/{comment}/like', [CommentController::class, 'like'])
    ->name('comments.like')
    ->middleware(['auth:sanctum']);

Route::post('comments/{comment}/unlike', [CommentController::class, 'unlike'])
    ->name('comments.unlike')
    ->middleware(['auth:sanctum']);