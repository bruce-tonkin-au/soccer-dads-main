<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SeasonsController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/reg/{memberCode}', [RegistrationController::class, 'show']);
Route::post('/reg/{memberCode}', [RegistrationController::class, 'update']);
Route::get('/seasons', [SeasonsController::class, 'index']);
Route::get('/seasons/{seasonKey}', [SeasonsController::class, 'show']);
Route::get('/seasons/{seasonKey}/{gameRound}', [SeasonsController::class, 'night']);

Route::get('/reg/{memberCode}.html', function($memberCode) {
    return redirect('/reg/' . $memberCode, 301);
});