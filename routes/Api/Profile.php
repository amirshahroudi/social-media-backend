<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/31/2022
 * Time: 8:38 PM
 */

use App\Http\Controllers\Api\Profile\AcceptFollowRequestController;
use App\Http\Controllers\Api\Profile\CheckUsernameIsAvailableController;
use App\Http\Controllers\Api\Profile\FollowUserController;
use App\Http\Controllers\Api\Profile\RejectFollowRequestController;
use App\Http\Controllers\Api\Profile\UnfollowUserController;
use App\Http\Controllers\Api\Profile\UpdateBioController;
use App\Http\Controllers\Api\Profile\UpdateNameController;
use App\Http\Controllers\Api\Profile\UpdatePrivacyController;
use App\Http\Controllers\Api\Profile\UpdateProfileImageController;
use App\Http\Controllers\Api\Profile\UpdateUsernameController;
use Illuminate\Support\Facades\Route;

/*
 * ------------------
 * follow
 * unfollow
 * accept-follow-request
 * reject-follow-request
 * ------------------
 * update name
 * update username
 * update bio
 * update image
 * ------------------
 * remove name
 * remove bio
 * remove image
 * ------------------
 */
Route::post('/follow/{user}', [FollowUserController::class, 'follow'])
    ->name('follow')
    ->middleware(['auth:sanctum']);

Route::post('/unfollow/{user}', [UnfollowUserController::class, 'unfollow'])
    ->name('unfollow')
    ->middleware(['auth:sanctum']);

Route::post('/accept-follow-request/{user}', [AcceptFollowRequestController::class, 'accept'])
    ->name('acceptFollowRequest')
    ->middleware(['auth:sanctum']);

Route::post('/reject-follow-request/{user}', [RejectFollowRequestController::class, 'reject'])
    ->name('rejectFollowRequest')
    ->middleware(['auth:sanctum']);

Route::name('update.')->prefix('update/')->middleware(['auth:sanctum'])
    ->group(function () {
        Route::post('/name', [UpdateNameController::class, 'updateName'])->name('name');
        Route::post('/bio', [UpdateBioController::class, 'updateBio'])->name('bio');
        Route::post('/username', [UpdateUsernameController::class, 'updateUsername'])->name('username');
        Route::post('/profile-image', [UpdateProfileImageController::class, 'updateProfileImage',
        ])->name('profileImage');
        Route::post('/privacy', [UpdatePrivacyController::class, 'updatePrivacy'])->name('privacy');
    });

Route::post('/is-username-available', [CheckUsernameIsAvailableController::class, 'isAvailable'])
    ->name('isUsernameAvailable');