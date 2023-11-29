<?php

use App\Http\Controllers\api\TransactionController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\VCardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;

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


Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('users/me', [UserController::class, 'show_me']);

    Route::post('vcards/confirm', [VCardController::class, 'isPhoneNumberAlreadyUsed']);

    // Get all transactions of last month of a vcard
    Route::get('vcard/{phone_number}/transactions/lastmonth', [VCardController::class, 'getTransactionsByPhoneNumberLastMonth']);

    // List transactions of a specific phone number
    Route::get('vcard/{phone_number}/transactions', [VCardController::class, 'getTransactionsByPhoneNumber']);

    // Statistics
    Route::get('statistics/vcards/count', [VCardController::class, 'getVCardCount']);
    Route::get('statistics/vcards/balance', [VCardController::class, 'getVCardBalanceSum']);
    Route::get('statistics/vcards/active/count', [VCardController::class, 'getActiveVCardCount']);
    Route::get('statistics/vcards/active/balance', [VCardController::class, 'getActiveVCardBalanceSum']);
    Route::get('statistics/transactions/count', [TransactionController::class, 'getTransactionsCount']);
    Route::get('statistics/transactions/sum', [TransactionController::class, 'getTransactionsSum']);

    /*
     * Globais
     */

    Route::apiResource('vcards', VCardController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('transactions', TransactionController::class);

});


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


