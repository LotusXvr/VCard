<?php

use App\Http\Controllers\api\AdminController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\DefaultCategoryController;
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
    Route::patch('vcards/{vcard}/password', [VCardController::class, 'update_password'])->middleware('can:updatePassword,vcard');
    Route::patch('vcards/{vcard}/change-status', [VCardController::class, 'changeStatus']);
    Route::patch('vcards/{vcard}/change-confirmation-code', [VCardController::class, 'update_confirmation_code'])->middleware('can:updatePassword,vcard');

    Route::post('vcards/confirm', [VCardController::class, 'isPhoneNumberAlreadyUsed']);

    // List transactions of a specific phone number
    Route::get('vcard/{vcard}/transactions', [VCardController::class, 'getTransactionsByPhoneNumber'])->middleware('can:view,vcard');
    Route::get('vcard/{vcard}/category', [VCardController::class, 'getCategoryFromVCard'])->middleware('can:view,vcard');
    Route::get("vcard/{vcard}/category/all", [VCardController::class, 'getCategoryFromVCardWithTrashed'])->middleware('can:view,vcard');
    Route::get('vcard/{vcard}/transactions/lastmonth', [VCardController::class, 'getLastMonthTransactionsByPhoneNumber'])->middleware('can:view,vcard');

    // Statistics
    Route::get('statistics/transactions/sum-between-dates', [TransactionController::class, 'getTransactionsSumBetweenDates']);
    Route::get('statistics/transactions/older', [TransactionController::class, 'getOlderTransaction']);
    Route::get('statistics/transactions/count-by-type', [TransactionController::class, 'getTransactionsCountByType']);

    Route::get("statistics/vcards", [VCardController::class, 'getVCardStatistics']);
    Route::get("statistics/transactions", [TransactionController::class, 'getTransactionStatistics']);
    /*
     * Globais
     */

    Route::apiResource('vcards', VCardController::class);
    Route::apiResource('category', CategoryController::class);
    Route::apiResource('default-category', DefaultCategoryController::class);
    Route::get('users', [UserController::class, 'index'])->middleware('can:viewAny,App\Models\User');
    Route::get('users/{user}',[UserController::class,'show'])->middleware('can:view,user');
    //Route::post('users', [UserController::class, 'store'])->middleware('can:create');
    Route::apiResource('admins', AdminController::class)->middleware('can:viewAny,App\Models\User');
    Route::apiResource('transactions', TransactionController::class);

});


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


