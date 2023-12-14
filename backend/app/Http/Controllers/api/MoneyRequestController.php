<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MoneyRequestResource;
use App\Models\VCard;
use App\MoneyRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoneyRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MoneyRequest::all();
    }

    /**
     * Display the specified resource.
     */
    public function show(MoneyRequest $moneyRequest)
    {
        return new MoneyRequestResource($moneyRequest);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /*
         *   This function will handle the creation of a new money request
         *   Parameters are: requesters' vcard (from_vcard), request receivers' vcard (to_vcard), value (amount), description (optional)
         *   The status of the request will be null by default, and will be updated to 'accepted' or 'rejected' by the receiver
         *   Represente by the values of the boolean 0 or 1 in the database (1 = accepted, 0 = rejected)
         *   The request will be sent to the receiver, and the receiver will be able to accept or reject it
         *   If the receiver accepts the request, the value will be transfered from the requesters' vcard to the receivers' vcard
         *   Adding that the only possible payment type is VCARD
         */

        // verify if value being sent is at least 0.01€
        if ($request->value < 0.01) {
            return response()->json(['message' => 'Minimum transfer amount is 0.01€'], 422);
        }

        // verify if sender is not sending money to himself
        if ($request->from_vcard == $request->to_vcard) {
            return response()->json(['message' => 'You cannot send money to yourself'], 422);
        }

        // Verify if destination vcard exists or is blocked
        $destinVCardExistsOrIsBlocked = VCard::where('phone_number', $request->payment_reference)
            ->whereNull('deleted_at')
            ->orWhere('blocked', 1)
            ->first();

        if (!$destinVCardExistsOrIsBlocked) {
            return response()->json(['message' => 'Destin VCard does not exist or is blocked'], 404);
        }

        try {
            DB::Transaction(function () use ($request) {
                $moneyRequest = new MoneyRequest();
                $moneyRequest->from_vcard = $request->from_vcard;
                $moneyRequest->to_vcard = $request->to_vcard;
                $moneyRequest->amount = $request->value;
                $moneyRequest->description = $request->description;
                $moneyRequest->status = null;
                $moneyRequest->save();
            });
        } catch (Exception $e) {
            return response()->json(['message' => 'Error creating money request', 'error' => $e->getMessage()], 500);
        }

    }

    public function acceptOrRejectMoneyRequest(Request $request){
        // here we need to check if the sender has accepted or not the request
        // but first we need to validate if he has enough money and etc

        $sendersVCard = VCard::where('phone_number', $request->vcard)->first();

        // verify if confirmation code is the correct one of the user
        if (!password_verify($request->confirmation_code, $sendersVCard->confirmation_code)) {
            return response()->json(['message' => 'Invalid confirmation code'], 422);
        }

        // verify if sender has enough money on account balance
        if ($sendersVCard->balance < $request->value) {
            return response()->json(['message' => 'Insuficient balance'], 422);
        }

        // verify if value being sent is higher than max_debit (invalid)
        if ($request->value > $sendersVCard->max_debit) {
            return response()->json(['message' => 'Value higher than maximum debit allowed'], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
