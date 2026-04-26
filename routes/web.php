<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SeasonsController;
use App\Http\Controllers\PlayersController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\PlayerAuthController;
use App\Http\Controllers\PlayerPortalController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/players', [PlayersController::class, 'index']);
Route::get('/players/{memberSlug}', [PlayersController::class, 'show']);
Route::get('/reg/{memberCode}', [RegistrationController::class, 'show']);
Route::post('/reg/{memberCode}', [RegistrationController::class, 'update']);
Route::get('/seasons', [SeasonsController::class, 'index']);
Route::get('/seasons/{seasonKey}', [SeasonsController::class, 'show']);
Route::get('/seasons/{seasonKey}/{gameRound}', [SeasonsController::class, 'night']);

Route::get('/about', [AboutController::class, 'index']);
Route::get('/about/history', [AboutController::class, 'history']);
Route::get('/about/locations', [AboutController::class, 'locations']);
Route::get('/about/honour-board', [AboutController::class, 'honourBoard']);

// Auth
Route::get('/login', [PlayerAuthController::class, 'showLogin']);
Route::post('/login', [PlayerAuthController::class, 'sendMagicLink']);
Route::get('/auth/{token}', [PlayerAuthController::class, 'authenticate']);
Route::post('/logout', [PlayerAuthController::class, 'logout']);

// Player portal
Route::middleware('player.auth')->group(function () {
    Route::get('/portal', [PlayerPortalController::class, 'index']);
    Route::get('/portal/profile', [PlayerPortalController::class, 'profile']);
    Route::post('/portal/profile', [PlayerPortalController::class, 'updateProfile']);
    Route::get('/portal/account', [PlayerPortalController::class, 'account']);
    Route::get('/portal/history', [PlayerPortalController::class, 'history']);
    Route::get('/portal/topup', [PlayerPortalController::class, 'topup']);
    Route::post('/portal/topup/create', [PlayerPortalController::class, 'createPayment']);
    Route::get('/portal/topup/success', [PlayerPortalController::class, 'paymentSuccess']);
    Route::get('/portal/topup/cancel', [PlayerPortalController::class, 'paymentCancel']);
});

Route::post('/stripe/webhook', [PlayerPortalController::class, 'stripeWebhook']);

Route::get('/r/{memberCode}', function($memberCode) {
    return redirect('/reg/' . $memberCode, 301);
});

Route::get('/r/{memberCode}.html', function($memberCode) {
    return redirect('/reg/' . $memberCode, 301);
});