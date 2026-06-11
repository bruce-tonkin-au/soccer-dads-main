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
use App\Http\Controllers\StoreController;
use App\Http\Controllers\AdminStoreController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\NewsletterController;
use App\Livewire\WorldCupLadder;

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
    Route::get('/seasons/{seasonID}/edit', [AdminController::class, 'editSeason']);
    Route::post('/seasons/{seasonID}/edit', [AdminController::class, 'updateSeason']);

    Route::get('/seasons/{seasonID}/games', [AdminController::class, 'games']);
    Route::get('/seasons/{seasonID}/games/create', [AdminController::class, 'createGame']);
    Route::post('/seasons/{seasonID}/games/create', [AdminController::class, 'storeGame']);
    Route::get('/seasons/{seasonID}/games/{gameID}/edit', [AdminController::class, 'editGame']);
    Route::post('/seasons/{seasonID}/games/{gameID}/edit', [AdminController::class, 'updateGame']);

    Route::post('/games/{gameID}/reset-night', [AdminController::class, 'resetNight']);

    Route::get('/teams/{gameID}', [AdminController::class, 'teams']);
    Route::post('/teams/{gameID}', [AdminController::class, 'saveTeams']);
    Route::post('/teams/{gameID}/bench/{memberID}', [AdminController::class, 'toggleBench']);
    Route::post('/registrations/{gameID}/promote/{memberID}', [AdminController::class, 'promotePlayer']);
    Route::post('/registrations/{gameID}/demote/{memberID}', [AdminController::class, 'demotePlayer']);
    Route::post('/registrations/{gameID}/register/{memberID}', [AdminController::class, 'registerPlayer']);

    Route::get('/ratings', [AdminController::class, 'ratings']);
    Route::get('/ratings/{memberID}', [AdminController::class, 'playerRatings']);

    Route::get('/print/{gameID}', [AdminController::class, 'printSheet']);

    Route::get('/messages', [AdminController::class, 'messages']);
    Route::get('/messages/create', [AdminController::class, 'createMessage']);
    Route::post('/messages/create', [AdminController::class, 'storeMessage']);
    Route::get('/messages/{messageCode}/edit', [AdminController::class, 'editMessage']);
    Route::post('/messages/{messageCode}/edit', [AdminController::class, 'updateMessage']);
    Route::get('/messages/{messageCode}/links', [AdminController::class, 'messageLinks']);

    Route::get('/seasons/{seasonID}/games/{gameID}/preview-charges', [AdminController::class, 'previewCharges']);
    Route::post('/seasons/{seasonID}/games/{gameID}/charge', [AdminController::class, 'processCharges']);

    Route::get('/finances', [AdminController::class, 'finances']);

    // Store management
    Route::get('/store/products', [AdminStoreController::class, 'products']);
    Route::get('/store/products/create', [AdminStoreController::class, 'createProduct']);
    Route::post('/store/products/create', [AdminStoreController::class, 'storeProduct']);
    Route::get('/store/products/{productID}/edit', [AdminStoreController::class, 'editProduct']);
    Route::post('/store/products/{productID}/edit', [AdminStoreController::class, 'updateProduct']);
    Route::post('/store/products/{productID}/toggle', [AdminStoreController::class, 'toggleProduct']);

    // Product image management
    Route::post('/store/products/{productID}/images/upload', [AdminStoreController::class, 'uploadProductImage']);
    Route::post('/store/products/{productID}/images/reorder', [AdminStoreController::class, 'reorderProductImages']);
    Route::post('/store/products/{productID}/images/{imageID}/delete', [AdminStoreController::class, 'deleteProductImage']);
    Route::post('/store/products/{productID}/images/{imageID}/primary', [AdminStoreController::class, 'setPrimaryImage']);

    Route::get('/store/orders', [AdminStoreController::class, 'orders']);
    Route::get('/store/orders/{orderID}/edit', [AdminStoreController::class, 'editOrder']);
    Route::post('/store/orders/{orderID}/edit', [AdminStoreController::class, 'updateOrder']);
    Route::post('/store/orders/{orderID}/delete', [AdminStoreController::class, 'deleteOrder']);
    Route::post('/store/orders/{orderID}/refund', [AdminStoreController::class, 'refundOrder']);

    Route::get('/registrations', [AdminController::class, 'registrations']);
    Route::get('/registrations/{gameID}', [AdminController::class, 'registrations']);

    Route::get('/commentators', [AdminController::class, 'commentators']);
    Route::get('/commentators/create', [AdminController::class, 'createCommentator']);
    Route::post('/commentators/create', [AdminController::class, 'storeCommentator']);
    Route::get('/commentators/{commentatorID}/edit', [AdminController::class, 'editCommentator']);
    Route::post('/commentators/{commentatorID}/edit', [AdminController::class, 'updateCommentator']);

    // Newsletters
    Route::get('/newsletters', [NewsletterController::class, 'index']);
    Route::get('/newsletters/create', [NewsletterController::class, 'create']);
    Route::post('/newsletters', [NewsletterController::class, 'store']);
    Route::get('/newsletters/{id}/edit', [NewsletterController::class, 'edit']);
    Route::put('/newsletters/{id}', [NewsletterController::class, 'update']);
    Route::delete('/newsletters/{id}', [NewsletterController::class, 'destroy']);
});

// Store — specific routes before the {productSlug} wildcard
Route::get('/store', [StoreController::class, 'index']);
Route::get('/store/order-complete', [StoreController::class, 'orderComplete']);
Route::get('/store/cart', [StoreController::class, 'viewCart']);
Route::post('/store/cart/checkout', [StoreController::class, 'cartCheckout']);
Route::post('/store/cart/remove', [StoreController::class, 'removeFromCart']);
Route::post('/store/{productSlug}/checkout', [StoreController::class, 'checkout']);
Route::post('/store/{productSlug}/add-to-cart', [StoreController::class, 'addToCart']);
Route::get('/store/{productSlug}', [StoreController::class, 'show']);

Route::get('/', [HomeController::class, 'index']);
Route::get('/players', [PlayersController::class, 'index']);
Route::get('/players/{memberSlug}', [PlayersController::class, 'show']);
Route::get('/players/{memberSlug}/card', [PlayersController::class, 'card']);
Route::get('/reg/{memberCode}', [RegistrationController::class, 'show'])->where('memberCode', '[^.]+');
Route::post('/reg/{memberCode}', [RegistrationController::class, 'update'])->where('memberCode', '[^.]+');
Route::get('/claim/{memberCode}', [ClaimController::class, 'show'])->where('memberCode', '[^./]+');
Route::post('/claim/{memberCode}', [ClaimController::class, 'store'])->where('memberCode', '[^./]+');
Route::get('/claim/{memberCode}/welcome', [ClaimController::class, 'welcome'])->where('memberCode', '[^./]+');
Route::get('/seasons', [SeasonsController::class, 'index']);
Route::get('/seasons/{seasonKey}', [SeasonsController::class, 'show']);
Route::get('/seasons/{seasonKey}/{gameRound}', [SeasonsController::class, 'night']);

Route::get('/worldcup', WorldCupLadder::class);

Route::get('/about', [AboutController::class, 'index']);
Route::get('/contact', [ContactController::class, 'index']);
Route::get('/msg/{messageCode}/newsletter', [MessageController::class, 'newsletter']);
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
    Route::post('/portal/birthday', [PlayerPortalController::class, 'saveBirthday']);
    Route::post('/portal/contacts', [PlayerPortalController::class, 'storeContact']);
    Route::post('/portal/contacts/{contactID}', [PlayerPortalController::class, 'updateContact']);
    Route::post('/portal/contacts/{contactID}/delete', [PlayerPortalController::class, 'deleteContact']);
    Route::post('/portal/contacts/{contactID}/primary', [PlayerPortalController::class, 'setPrimaryContact']);
});

Route::get('/topup/{memberCode}', [PlayerPortalController::class, 'publicTopup']);
Route::post('/topup/{memberCode}/create', [PlayerPortalController::class, 'publicCreatePayment']);
Route::get('/topup/{memberCode}/success', [PlayerPortalController::class, 'publicPaymentSuccess']);
Route::get('/topup/{memberCode}/cancel', [PlayerPortalController::class, 'publicPaymentCancel']);

Route::post('/stripe/webhook', [PlayerPortalController::class, 'stripeWebhook']);

Route::get('/rate/{memberCode}', [RatingController::class, 'show']);
Route::post('/rate/{memberCode}', [RatingController::class, 'store']);
Route::get('/rate/{memberCode}/done', [RatingController::class, 'done']);

Route::get('/reg/{memberCode}.html', function($memberCode) {
    return redirect('/reg/' . $memberCode, 301);
});

Route::get('/r/{memberCode}', function($memberCode) {
    return redirect('/reg/' . $memberCode, 301);
});

Route::get('/r/{memberCode}.html', function($memberCode) {
    return redirect('/reg/' . $memberCode, 301);
});