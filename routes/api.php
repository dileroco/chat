<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SendMSGController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\fetchUsersController;
Route::middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
])->group(function () {




    Route::get('/users', [fetchUsersController::class, 'fetchUsers']);

    Route::match(['get','post'],'/profile',[ProfileController::class,'fetch']);
    Route::post('/send-message', [SendMSGController::class, 'sendMessage']);
    Route::post('/messages', [MessageController::class, 'fetchMessages']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/contacts', [ContactController::class, 'contacts']);
});
