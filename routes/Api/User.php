<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 2/16/2022
 * Time: 8:09 PM
 */

use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

Route::get('{username}/followers', [UserController::class, 'getFollowers'])
    ->name('users.followers')
    ->middleware(['auth:sanctum']);

Route::get('{username}/followings', [UserController::class, 'getFollowings'])
    ->name('users.followings')
    ->middleware(['auth:sanctum']);

Route::get('{username}/posts', [UserController::class, 'getPosts'])
    ->name('users.posts')
    ->middleware(['auth:sanctum']);

Route::get('{username}/profileImage', [UserController::class, 'getProfileImage'])
    ->name('users.profile')
    ->middleware(['auth:sanctum']);

Route::get('{username}/info', [UserController::class, 'getUserInfo'])
    ->name('users.info')
    ->middleware(['auth:sanctum']);