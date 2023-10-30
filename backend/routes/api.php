<?php

use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\VCardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('vcards', [VCardController::class, 'index']);


Route::get('vcards/{vcard}', [VCardController::class, 'getVCard']);


Route::get('users', [UserController::class, 'index']);

Route::get('users/{user}', [UserController::class, 'getUser']);
