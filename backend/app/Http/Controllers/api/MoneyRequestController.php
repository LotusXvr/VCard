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

    public function acceptOrRejectMoneyRequest(Request $request, MoneyRequest $moneyRequest)
    {

        /*
         *   This function will receive the MoneyRequest in question
         *   And in the $request variable it will receive the confirmation code of the
         *   accepter's or rejecter's vcard and also the status to verify if
         *   he accepted or rejected the request
         */

        if ($request->status == 0) {
            // handle the rejection of the request
            $this->update($request->status, $moneyRequest);

            // as the request was rejected, we dont need to do anything else
            return response()->json(['message' => 'Money request rejected'], 200);
        }


        // verify now if the request was accepted
        if ($request->status == 1) {
            // handle the acceptance of the request
            $this->update($request->status, $moneyRequest);

            // handle the start of the transaction process
            try {
                Request::create('transactions', 'POST', [
                    // Your request data here, e.g., input parameters
                    'vcard' => $moneyRequest->to_vcard,
                    'payment_reference' => $moneyRequest->from_vcard,
                    'value' => $moneyRequest->amount,
                    'confirmation_code' => $request->confirmation_code,
                    'description' => $moneyRequest->description,
                ]);
            } catch (Exception $e) {
                return response()->json(['message' => 'Error creating the money request acceptance transaction', 'error' => $e->getMessage()], 500);
            }

        }

        return response()->json(['message' => 'Money request handled correctly'], 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(bool $status, MoneyRequest $moneyRequest)
    {
        try {
            $moneyRequest->status = $status;
            $moneyRequest->save();
        } catch (Exception $e) {
            return response()->json(['message' => 'Error updating money request', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Money request updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
