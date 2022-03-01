<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/27/2022
 * Time: 2:35 PM
 */

use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth API Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [LoginController::class, 'login'])
    ->name('login');

Route::post('/logout', [LogoutController::class, 'logout'])
    ->name('logout')
    ->middleware(['auth:sanctum']);

Route::post('/register', [RegisterController::class, 'register'])
    ->name('register');

Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword'])
    ->name('password.forgot');

Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])
    ->name('password.reset');