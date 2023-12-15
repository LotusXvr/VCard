<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MoneyRequestResource;
use App\Models\VCard;
use App\Models\MoneyRequest;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

        return response()->json(['message' => 'Money request created successfully'], 200);

    }

    public function acceptOrRejectMoneyRequest(MoneyRequest $moneyRequest, Request $request)
    {

        /*
         *   This function will receive the MoneyRequest in question
         *   And in the $request variable it will receive the confirmation code of the
         *   accepter's or rejecter's vcard and also the status to verify if
         *   he accepted or rejected the request
         */

        // check if money request being handled has already been canceled or accepted
        $moneyRequest = MoneyRequest::where('id', $moneyRequest->id)->first();
        if ($moneyRequest->status != null) {
            return response()->json(['message' => 'Money request has already been canceled or accepted'], 422);
        }

        if ($request->status == 0) {
            // handle the rejection of the request
            $this->update($request->status, $moneyRequest);

            // as the request was rejected, we dont need to do anything else
            return response()->json(['message' => 'Money request rejected successfully'], 200);
        }


        // If the money request is different than null and is not 0, than it should be 1, no need to validate anything
        // More. From now on, lets handle the acceptance of the request


        // verify if confirmation code is the correct one of the user
        $vcardAccepter = VCard::where('phone_number', $moneyRequest->to_vcard)->first();
        if (!password_verify($request->confirmation_code, $vcardAccepter->confirmation_code)) {
            return response()->json(['message' => 'Invalid confirmation code'], 422);
        }

        // verify if sender has enough money on account balance
        if ($vcardAccepter->balance < $request->value) {
            return response()->json(['message' => 'Insuficient balance'], 422);
        }

        // verify if value being sent is higher than max_debit (invalid)
        if ($request->value > $vcardAccepter->max_debit) {
            return response()->json(['message' => 'Value higher than maximum debit allowed'], 422);
        }

        // In between the time of the request and the acceptance, the sender could have blocked his account
        $destinVCardExistsOrIsBlocked = VCard::where('phone_number', $moneyRequest->from_vcard)
            ->whereNull('deleted_at')
            ->orWhere('blocked', 1)
            ->first();
        if (!$destinVCardExistsOrIsBlocked) {
            return response()->json(['message' => 'Destin VCard does not exist or is blocked'], 404);
        }

        // handle the start of the transaction process
        try {

            $requestTransaction = [
                'vcard' => $moneyRequest->to_vcard,
                'payment_reference' => $moneyRequest->from_vcard,
                'type' => 'D',
                'payment_type' => 'VCARD',
                'value' => $moneyRequest->amount,
                'description' => $moneyRequest->description,
            ];

            // Create a transaction
            DB::transaction(function () use ($requestTransaction, $vcardAccepter) {

                $date = date('Y-m-d');
                $datetime = date('Y-m-d H:i:s');

                // Money sending transaction
                $transaction1 = new Transaction();
                $transaction1->vcard = $requestTransaction['vcard'];
                $transaction1->date = $date;
                $transaction1->datetime = $datetime;
                $transaction1->type = 'D';
                $transaction1->value = $requestTransaction['value'];
                $vcardBalance = $vcardAccepter->balance;
                $transaction1->old_balance = $vcardBalance;
                $transaction1->new_balance = $vcardBalance - $requestTransaction['value'];
                $transaction1->payment_type = $requestTransaction['payment_type'];
                $transaction1->payment_reference = $requestTransaction['payment_reference'];
                $transaction1->pair_vcard = $requestTransaction['payment_reference'];
                $transaction1->category_id = null;
                $transaction1->description = $requestTransaction['description'];

                // Money reception transaction
                $transaction2 = new Transaction();
                $transaction2->vcard = $requestTransaction['payment_reference'];
                $transaction2->date = $date;
                $transaction2->datetime = $datetime;
                $transaction2->type = 'C';
                $transaction2->value = $requestTransaction['value'];
                $payment_referenceBalance = VCard::where('phone_number', $requestTransaction['payment_reference'])->first()->balance;
                $transaction2->old_balance = $payment_referenceBalance;
                $transaction2->new_balance = $payment_referenceBalance + $requestTransaction['value'];
                $transaction2->payment_type = $requestTransaction['payment_type'];
                $transaction2->payment_reference = $requestTransaction['vcard'];
                $transaction2->pair_vcard = $requestTransaction['vcard'];
                $transaction2->category_id = null;
                $transaction2->description = $requestTransaction['description'];

                // Save transactions to get their id's
                $transaction1->save();
                $transaction2->save();

                // Update pair_transaction properties
                $transaction1->pair_transaction = $transaction2->id;
                $transaction2->pair_transaction = $transaction1->id;

                // Save transactions again to update pair_transaction values
                $transaction1->save();
                $transaction2->save();

                // Update both individual's balances
                VCard::where('phone_number', $requestTransaction['vcard'])->update(['balance' => $transaction1->new_balance]);
                VCard::where('phone_number', $requestTransaction['payment_reference'])->update(['balance' => $transaction2->new_balance]);
            });



            // handle the acceptance of the request
            $this->update($request->status, $moneyRequest);

            return response()->json(['message' => 'Money request accepted and transaction created successfully'], 200);

        } catch (Exception $e) {
            return response()->json(['message' => 'Error creating the money request acceptance transaction', 'error' => $e->getMessage()], 500);
        }



    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $status, MoneyRequest $moneyRequest)
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
