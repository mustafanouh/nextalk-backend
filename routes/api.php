<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SignalingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:6,1');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1');

    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:3,1');

    Route::post('reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:3,1');
});

// Clicked from the verification email — deliberately outside auth:sanctum
// since the browser tab that opens it has no Bearer token.
// Security comes from the 'signed' middleware.
Route::get(
    'auth/email/verify/{id}/{hash}',
    VerifyEmailController::class
)
    ->middleware('signed')
    ->name('verification.verify');


// ── Protected (Sanctum) ──────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // ── Authentication ───────────────────────────────────────────

    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });


    // ── Users ────────────────────────────────────────────────────

    Route::prefix('users')->group(function () {
        Route::get('me', [UserController::class, 'me']);
        Route::patch('me', [UserController::class, 'updateMe']);
        Route::get('search', [UserController::class, 'search']);
        Route::get('{user:username}', [UserController::class, 'show']);
    });


    // ── Conversations ────────────────────────────────────────────

    Route::apiResource('conversations', ConversationController::class)
        ->only([
            'index',
            'store',
            'show',
        ]);


    // ── Messages ────────────────────────────────────────────────

    Route::get(
        'conversations/{conversation}/messages',
        [MessageController::class, 'index']
    );

    Route::post(
        'conversations/{conversation}/messages',
        [MessageController::class, 'store']
    );

    Route::delete(
        'messages/{message}',
        [MessageController::class, 'destroy']
    );


    // ── Calls ────────────────────────────────────────────────────

    Route::post(
        'conversations/{conversation}/calls',
        [CallController::class, 'store']
    );

    Route::post(
        'calls/{call}/accept',
        [CallController::class, 'accept']
    );

    Route::post(
        'calls/{call}/reject',
        [CallController::class, 'reject']
    );

    Route::post(
        'calls/{call}/end',
        [CallController::class, 'end']
    );


    // ── WebRTC Signaling ─────────────────────────────────────────

    Route::post(
        'calls/{call}/signal/offer',
        [SignalingController::class, 'offer']
    );

    Route::post(
        'calls/{call}/signal/answer',
        [SignalingController::class, 'answer']
    );

    Route::post(
        'calls/{call}/signal/ice-candidate',
        [SignalingController::class, 'iceCandidate']
    );
});