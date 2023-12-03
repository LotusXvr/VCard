<?php

use App\Http\Controllers\api\AdminController;
use App\Http\Controllers\api\CategoryController;
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

// login and register
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::post('vcard', [VCardController::class, 'store']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('users/me', [UserController::class, 'show_me']);
    Route::patch('admins/{admin}/password', [AdminController::class, 'update_password']);
    Route::patch('vcards/{vcard}/password', [VCardController::class, 'update_password']);

    Route::post('vcards/confirm', [VCardController::class, 'isPhoneNumberAlreadyUsed']);

    // Get all transactions of last month of a vcard
    Route::get('vcard/{phone_number}/transactions/lastmonth', [VCardController::class, 'getTransactionsByPhoneNumberLastMonth']);

    // List transactions of a specific phone number
    Route::get('vcard/{phone_number}/transactions', [VCardController::class, 'getTransactionsByPhoneNumber']);
    Route::get('vcard/{vcard}/category', [VCardController::class, 'getCategoryFromVCard']);

    // Statistics
    Route::get('statistics/vcards/count', [VCardController::class, 'getVCardCount']);
    Route::get('statistics/vcards/balance', [VCardController::class, 'getVCardBalanceSum']);
    Route::get('statistics/vcards/active/count', [VCardController::class, 'getActiveVCardCount']);
    Route::get('statistics/vcards/active/balance', [VCardController::class, 'getActiveVCardBalanceSum']);
    Route::get('statistics/transactions/count', [TransactionController::class, 'getTransactionsCount']);
    Route::get('statistics/transactions/sum', [TransactionController::class, 'getTransactionsSum']);
    Route::get('statistics/transactions/sum-between-dates', [TransactionController::class, 'getTransactionsSumBetweenDates']);
    Route::get('statistics/transactions/older', [TransactionController::class, 'getOlderTransaction']);
    Route::get('statistics/transactions/count-by-type', [TransactionController::class, 'getTransactionsCountByType']);
    /*
     * Globais
     */

    Route::apiResource('vcards', VCardController::class);
    Route::apiResource('category', CategoryController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('admins', AdminController::class)->middleware('can:viewAny,App\Models\User');
    Route::apiResource('transactions', TransactionController::class);

});


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


