<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SeasonsController;
use App\Http\Controllers\PlayersController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PlayerAuthController;
use App\Http\Controllers\PlayerPortalController;

// Admin auth
Route::get('/admin/login', [AdminController::class, 'showLogin']);
Route::post('/admin/login', [AdminController::class, 'login']);
Route::post('/admin/logout', [AdminController::class, 'logout']);

// Admin area
Route::middleware('admin.auth')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard']);

    Route::get('/players', [AdminController::class, 'players']);
    Route::get('/players/create', [AdminController::class, 'createPlayer']);
    Route::post('/players/create', [AdminController::class, 'storePlayer']);
    Route::get('/players/{memberID}/edit', [AdminController::class, 'editPlayer']);
    Route::post('/players/{memberID}/edit', [AdminController::class, 'updatePlayer']);

    Route::get('/seasons', [AdminController::class, 'seasons']);
    Route::get('/seasons/create', [AdminController::class, 'createSeason']);
    Route::post('/seasons/create', [AdminController::class, 'storeSeason']);
    Route::get('/seasons/{seasonKey}/edit', [AdminController::class, 'editSeason']);
    Route::post('/seasons/{seasonKey}/edit', [AdminController::class, 'updateSeason']);

    Route::get('/seasons/{seasonKey}/games', [AdminController::class, 'games']);
    Route::get('/seasons/{seasonKey}/games/create', [AdminController::class, 'createGame']);
    Route::post('/seasons/{seasonKey}/games/create', [AdminController::class, 'storeGame']);
    Route::get('/seasons/{seasonKey}/games/{gameID}/edit', [AdminController::class, 'editGame']);
    Route::post('/seasons/{seasonKey}/games/{gameID}/edit', [AdminController::class, 'updateGame']);

    Route::get('/teams/{gameID}', [AdminController::class, 'teams']);
    Route::post('/teams/{gameID}', [AdminController::class, 'saveTeams']);

    Route::get('/ratings', [AdminController::class, 'ratings']);
    Route::get('/ratings/{memberID}', [AdminController::class, 'playerRatings']);

    Route::get('/print/{gameID}', [AdminController::class, 'printSheet']);

    Route::get('/messages', [AdminController::class, 'messages']);
    Route::get('/messages/create', [AdminController::class, 'createMessage']);
    Route::post('/messages/create', [AdminController::class, 'storeMessage']);
    Route::get('/messages/{messageCode}/edit', [AdminController::class, 'editMessage']);
    Route::post('/messages/{messageCode}/edit', [AdminController::class, 'updateMessage']);
    Route::get('/messages/{messageCode}/links', [AdminController::class, 'messageLinks']);
});

Route::get('/', [HomeController::class, 'index']);
Route::get('/players', [PlayersController::class, 'index']);
Route::get('/players/{memberSlug}', [PlayersController::class, 'show']);
Route::get('/players/{memberSlug}/card', [PlayersController::class, 'card']);
Route::get('/reg/{memberCode}', [RegistrationController::class, 'show']);
Route::post('/reg/{memberCode}', [RegistrationController::class, 'update']);
Route::get('/seasons', [SeasonsController::class, 'index']);
Route::get('/seasons/{seasonKey}', [SeasonsController::class, 'show']);
Route::get('/seasons/{seasonKey}/{gameRound}', [SeasonsController::class, 'night']);

Route::get('/about', [AboutController::class, 'index']);
Route::get('/contact', [ContactController::class, 'index']);
Route::get('/msg/{messageCode}/{memberCode}', [MessageController::class, 'show']);
Route::get('/contact.html', fn() => redirect('/contact', 301));
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

Route::get('/rate/{memberCode}', [RatingController::class, 'show']);
Route::post('/rate/{memberCode}', [RatingController::class, 'store']);
Route::get('/rate/{memberCode}/done', [RatingController::class, 'done']);

Route::get('/r/{memberCode}', function($memberCode) {
    return redirect('/reg/' . $memberCode, 301);
});

Route::get('/r/{memberCode}.html', function($memberCode) {
    return redirect('/reg/' . $memberCode, 301);
});