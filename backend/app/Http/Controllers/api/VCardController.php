<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateVCardRequest;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\VCardResource;
use App\Models\Transaction;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Models\VCard;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VCardController extends Controller
{

    public function index()
    {
        return VCard::all();
    }

    public function show(VCard $vcard)
    {
        return new VCardResource($vcard);
    }

    public function store(CreateVCardRequest $request)
    {
        $data = $request->validated();
        $data['blocked'] = 0; // Set blocked to 0
        $data['max_debit'] = 5000;
        $data['balance'] = 0;
        $vcard = VCard::create($data);
        return new VCardResource($vcard);
    }


    public function update(Request $request, VCard $vcard)
    {
        $vcard->fill($request->all());
        $vcard->save();
        return new VCardResource($vcard);
    }

    public function destroy(VCard $vcard)
    {
        $vcard->delete();
        return new VCardResource($vcard);
    }

    public function isPhoneNumberAlreadyUsed(Request $request)
    {
        $existingVCard = VCard::where('phone_number', $request->phone)->first();

        if ($existingVCard) {
            return response()->json(['message' => 'Phone number is already in use'], 422);
        }

        return response()->json(['message' => 'Phone number is available']);
    }

    public function getTransactionsByPhoneNumber(Request $request)
    {
        $phoneNumber = $request->phone_number;

        $transactions = Transaction::where('vcard', $phoneNumber)
            ->orderBy('date', 'desc')
            ->get();

        return TransactionResource::collection($transactions);
    }

    // get current count of vcards
    public function getVCardCount()
    {
        $vcardCount = VCard::count();

        return response()->json(['vcardCount' => $vcardCount]);
    }

    // get current count of active vcards
    public function getActiveVCardCount()
    {
        $activeVCardCount = VCard::where('blocked', 0)->count();

        return response()->json(['activeVCardCount' => $activeVCardCount]);
    }

    // get sum of all vcard balances
    public function getVCardBalanceSum()
    {
        $vcardBalanceSum = VCard::sum('balance');

        return response()->json(['vcardBalanceSum' => $vcardBalanceSum]);
    }

    // get sum of all active vcard balances
    public function getActiveVCardBalanceSum()
    {
        $activeVCardBalanceSum = VCard::where('blocked', 0)->sum('balance');

        return response()->json(['activeVCardBalanceSum' => $activeVCardBalanceSum]);
    }

}
