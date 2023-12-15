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
use App\Http\Controllers\api\MoneyRequestController;

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
    Route::patch('admins/{admin}/password', [AdminController::class, 'update_password'])->middleware('can:updatePassword,App\Models\User');
    Route::patch('vcards/{vcard}/password', [VCardController::class, 'update_password'])->middleware('can:updatePassword,vcard');
    Route::patch('vcards/{vcard}/change-status', [VCardController::class, 'changeStatus'])->middleware('can:changeStatus,App\Models\User');
    Route::patch('vcards/{vcard}/change-confirmation-code', [VCardController::class, 'update_confirmation_code'])->middleware('can:updatePassword,vcard');
    Route::delete('vcards/{vcard}/dismiss', [VCardController::class, 'dismissVCard'])->middleware('can:delete,vcard');

    Route::post('vcards/confirm', [VCardController::class, 'isPhoneNumberAlreadyUsed']);
    Route::post('vcards/{vcard}/reforcarPoupanca', [VCardController::class, 'reforcarPoupanca'])->middleware('can:updateSavings,vcard');
    Route::post('vcards/{vcard}/retirarPoupanca', [VCardController::class, 'retirarPoupanca'])->middleware('can:updateSavings,vcard');

    Route::put('vcard/{vcard}/spins', [VCardController::class, 'updateSpins']);

    // List transactions of a specific phone number
    Route::get('vcard/{vcard}/transactions', [VCardController::class, 'getTransactionsByPhoneNumber'])->middleware('can:view,vcard');
    Route::get('vcard/{vcard}/category', [VCardController::class, 'getCategoryFromVCard'])->middleware('can:view,vcard');
    Route::get("vcard/{vcard}/category/all", [VCardController::class, 'getCategoryFromVCardWithTrashed'])->middleware('can:view,vcard');
    Route::get('vcard/{vcard}/transactions/lastmonth', [VCardController::class, 'getLastMonthTransactionsByPhoneNumber'])->middleware('can:view,vcard');

    // Statistics
    Route::get('statistics/transactions/sum-between-dates', [TransactionController::class, 'getTransactionsSumBetweenDates'])->middleware('can:viewAny,App\Models\User');
    Route::get('statistics/transactions/older', [TransactionController::class, 'getOlderTransaction'])->middleware('can:viewAny,App\Models\User');
    Route::get('statistics/transactions/count-by-type', [TransactionController::class, 'getTransactionsCountByType'])->middleware('can:viewAny,App\Models\User');

    Route::get("statistics/vcards", [VCardController::class, 'getVCardStatistics'])->middleware('can:viewAny,App\Models\User');
    Route::get("statistics/transactions", [TransactionController::class, 'getTransactionStatistics'])->middleware('can:viewAny,App\Models\User');

    // Accept or reject money request
    Route::post("moneyRequests/{moneyRequest}/update", [MoneyRequestController::class, 'acceptOrRejectMoneyRequest']);
    /*
     * Globais
     */
    Route::get('vcards', [VCardController::class, 'index'])->middleware('can:viewAny,App\Models\User');
    Route::get('vcards/{vcard}', [VCardController::class, 'show'])->middleware('can:view,vcard');
    Route::put('vcards/{vcard}', [VCardController::class, 'update'])->middleware('can:update,vcard');
    Route::delete('vcards/{vcard}', [VCardController::class, 'destroy'])->middleware('can:viewAny,App\Models\User');
    Route::apiResource('category', CategoryController::class);
    Route::get('default-category', [DefaultCategoryController::class, 'index']);
    Route::get('default-category/{default_category}', [DefaultCategoryController::class, 'show']);
    Route::post('default-category', [DefaultCategoryController::class, 'store'])->middleware('can:create,App\Models\User');
    Route::put('default-category/{default_category}', [DefaultCategoryController::class, 'update']);
    Route::delete('default-category/{default_category}', [DefaultCategoryController::class, 'destroy'])->middleware('can:deleteDefaultCategory,App\Models\User');
    Route::get('users', [UserController::class, 'index'])->middleware('can:viewAny,App\Models\User');
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('can:view,user');
    Route::apiResource('admins', AdminController::class)->middleware('can:viewAny,App\Models\User');
    Route::get('transactions', [TransactionController::class , 'index'])->middleware('can:viewAny,App\Models\User');
    Route::post('transactions', [TransactionController::class , 'store']);
    Route::get('transactions/{transaction}', [TransactionController::class , 'show']);
    Route::put('transactions/{transaction}', [TransactionController::class , 'update']);
    Route::apiResource('moneyRequests', MoneyRequestController::class);

});


