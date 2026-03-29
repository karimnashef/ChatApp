<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/****************** API Routes ******************/

Route::prefix('v1')->group(function () {

    // Public auth routes
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/reset_password', [AuthController::class, 'resetPassword']);
    Route::post('auth/forget_security_key', [AuthController::class, 'forgetSecurityKey']);
    Route::post('telegram/webhook', [TelegramController::class, 'webhook']);

    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/logout_all', [AuthController::class, 'logoutAll']);
        Route::post('auth/refresh_token', [AuthController::class, 'refreshToken']);

        // User CRUD
        Route::apiResource('users', UserController::class);

        // Posts
        Route::apiResource('posts', PostController::class);
        Route::post('posts/{post}/like', [PostController::class, 'like']);
        Route::delete('posts/{post}/like', [PostController::class, 'unlike']);
        Route::get('posts/{post}/comments', [PostCommentController::class, 'index']);
        Route::post('posts/{post}/comments', [PostCommentController::class, 'store']);
        Route::delete('posts/{post}/comments/{comment}', [PostCommentController::class, 'destroy']);

        // Friend requests
        Route::get('friend-requests', [FriendController::class, 'index']);
        Route::post('friend-requests', [FriendController::class, 'store']);
        Route::post('friend-requests/{request}/accept', [FriendController::class, 'accept']);
        Route::post('friend-requests/{request}/decline', [FriendController::class, 'decline']);
        Route::get('friends', [FriendController::class, 'index']);
        Route::delete('friends/{friend}', [FriendController::class, 'destroy']);

        // Chat
        Route::get('chats', [ChatController::class, 'index']);
        Route::post('chats', [ChatController::class, 'store']);
        Route::get('chats/{chat}', [ChatController::class, 'show']);
        Route::get('chats/{chat}/messages', [MessageController::class, 'index']);
        Route::post('chats/{chat}/messages', [MessageController::class, 'store']);
        Route::post('messages/{message}/read', [MessageController::class, 'markAsRead']);
    });

});


?>
