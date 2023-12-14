<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\VCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Transaction::all();

    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /*
         * - recebe:
         * vcard, value, confirmation_code e payment_reference(vcard destino ou referencia)
         * posteriormente adiconou-se o type (C ou D) e payment_type (VCARD, IBAN, PAYPAL, VISA, MB, MBWAY)
         * - precisa de preencher:
         * vcard, date, datetime, type, value, old_balance, new_balamce, payment_type,
         * payment_reference, pair_transaction, pair_vcard, category_id, description (nao obrigatorio)
         *
         * no caso da referencia destino ser um vcard, cria uma outra transação ligada por
         * pair_transaction e pair_vcard (null em caso contrario)
         * sabemos que o destinatário é vcard se o parametro payment_type for VCARD
         */

        // FIRST VALIDATIONS

        // credit transactions are only handled by admins so they dont have balance nor do they have confirmation_codes
        // this makes so that only debit transations (the one made by users) validate the confirmation_code
        // and the balance of the user (adminds dont have balance)

        if ($request->type != 'C') {
            // verify if confirmation code is the correct one of the user
            $vcardOrigin = VCard::where('phone_number', $request->vcard)->first();
            if (!password_verify($request->confirmation_code, $vcardOrigin->confirmation_code)) {
                return response()->json(['message' => 'Invalid confirmation code'], 422);
            }

            // verify if sender has enough money on account balance
            if ($vcardOrigin->balance < $request->value) {
                return response()->json(['message' => 'Insuficient balance'], 422);
            }

            // verify if value being sent is higher than max_debit (invalid)
            if ($request->value > $vcardOrigin->max_debit) {
                return response()->json(['message' => 'Value higher than maximum debit allowed'], 422);
            }

            // verify if sender is not sending money to himself
            if ($request->vcard == $request->payment_reference) {
                return response()->json(['message' => 'You cannot send money to yourself'], 422);
            }
        }

        // verify if value being sent is at least 0.01€
        if ($request->value < 0.01) {
            return response()->json(['message' => 'Minimum transfer amount is 0.01€'], 422);
        }

        // VCARD

        if ($request->payment_type == 'VCARD') {
            // Verify if destination vcard exists or is blocked
            $destinVCardExistsOrIsBlocked = VCard::where('phone_number', $request->payment_reference)
                ->whereNull('deleted_at')
                ->orWhere('blocked', 1)
                ->first();
            if (!$destinVCardExistsOrIsBlocked) {
                return response()->json(['message' => 'Destin VCard does not exist or is blocked'], 404);
            }

            try {
                DB::transaction(function () use ($request) {

                    $date = date('Y-m-d');
                    $datetime = date('Y-m-d H:i:s');

                    // Money sending transaction
                    if ($request->type != 'C') {
                        $transaction1 = new Transaction();
                        $transaction1->vcard = $request->vcard;
                        $transaction1->date = $date;
                        $transaction1->datetime = $datetime;
                        $transaction1->type = 'D'; // como o utilizador está a enviar dinheiro, a primeira operação é sempre Debito
                        $transaction1->value = $request->value;
                        $vcardBalance = VCard::where('phone_number', $request->vcard)->first()->balance;
                        $transaction1->old_balance = $vcardBalance;
                        $transaction1->new_balance = $vcardBalance - $request->value;
                        $transaction1->payment_type = $request->payment_type;
                        $transaction1->payment_reference = $request->payment_reference;
                        $transaction1->pair_vcard = $request->payment_reference;
                        $transaction1->category_id = $request->category_id;
                        $transaction1->description = $request->description;
                    }


                    // Money reception transaction
                    $transaction2 = new Transaction();
                    $transaction2->vcard = $request->payment_reference;
                    $transaction2->date = $date;
                    $transaction2->datetime = $datetime;
                    $transaction2->type = 'C'; // tendo em conta esta operaçao ser a inversa, esta será de Crédito
                    $transaction2->value = $request->value;
                    $payment_referenceBalance = VCard::where('phone_number', $request->payment_reference)->first()->balance;
                    $transaction2->old_balance = $payment_referenceBalance;
                    $transaction2->new_balance = $payment_referenceBalance + $request->value;
                    $transaction2->payment_type = $request->payment_type;
                    $transaction2->payment_reference = $request->vcard;
                    $transaction2->pair_transaction = ($request->type != 'C') ? $transaction1->id : null;
                    $transaction2->pair_vcard = ($request->type != 'C') ? $request->vcard : null;
                    $transaction2->category_id = null;
                    $transaction2->description = null;


                    if ($request->type != 'C') {
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
                        VCard::where('phone_number', $request->vcard)->update(['balance' => $transaction1->new_balance]);
                        VCard::where('phone_number', $request->payment_reference)->update(['balance' => $transaction2->new_balance]);
                    } else {
                        $transaction2->save();
                        VCard::where('phone_number', $request->payment_reference)->update(['balance' => $transaction2->new_balance]);
                    }

                });



            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error creating transaction',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        // ANY OTHER PAYMENT TYPE
        elseif (in_array($request->payment_type, ['IBAN', 'PAYPAL', 'VISA', 'MB', 'MBWAY'])) {

            $endpoint = ($request->type == 'D') ? 'credit' : 'debit';

            // API call
            $response = Http::post("https://dad-202324-payments-api.vercel.app/api/{$endpoint}", [
                'type' => $request->payment_type,
                'reference' => $request->payment_reference,
                'value' => (float) $request->value,
            ]);

            // Check the response and handle accordingly
            if (!$response->successful()) {
                $responseData = $response->json(); // Parse JSON response
                $message = isset($responseData['message']) ? $responseData['message'] : '(api) Error sending transaction';
                return response()->json(['message' => $message], $response->status());
            }


            $vcard = VCard::where('phone_number', $request->vcard)->first();

            try {
                DB::transaction(function () use ($request, $vcard) {
                    // Money sending transaction
                    $transaction = new Transaction();

                    $transaction->vcard = $request->vcard;
                    $transaction->date = date('Y-m-d');
                    $transaction->datetime = date('Y-m-d H:i:s');
                    $transaction->type = $request->type;
                    $transaction->value = $request->value;
                    $vcardBalance = $vcard->balance;
                    $transaction->old_balance = $vcardBalance;
                    $transaction->new_balance = ($request->type == 'D') ? $vcardBalance - $request->value : $vcardBalance + $request->value;
                    $transaction->payment_type = $request->payment_type;
                    $transaction->payment_reference = $request->payment_reference;
                    $transaction->pair_vcard = null;
                    $transaction->pair_transaction = null;
                    $transaction->category_id = $request->category_id;
                    $transaction->description = $request->description;

                    $transaction->save();

                    // give the user 1 spin for every 10 euros sent
                    $vcard->spins += floor($request->value / 10);
                    VCard::where('phone_number', $request->vcard)->update(['spins' => $vcard->spins, 'balance' => $transaction->new_balance]);

                });



            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error creating transaction',
                    'error' => $e->getMessage()
                ], 500);
            }

        } else {
            return response()->json(['message' => 'Invalid payment type'], 401);
        }

        return response()->json(['message' => "{$request->value}€ sent to {$request->payment_reference} successfully", "spins" => floor($request->value / 10)], 200);

    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $data = $request->all();

        // Update the transaction with the extracted data
        $transaction->update($data);

        // Return the updated transaction
        return new TransactionResource($transaction);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }


    public function getTransactionsSumBetweenDates(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $sumBetweenDates = Transaction::whereBetween('date', [$startDate, $endDate])->sum('value');
        $countBetweenDates = Transaction::whereBetween('date', [$startDate, $endDate])->count();

        return response()->json(['sumBetweenDates' => $sumBetweenDates, 'countBetweenDates' => $countBetweenDates]);
    }

    public function getOlderTransaction(Request $request)
    {

        $olderTransaction = Transaction::orderBy('date')->first();

        return response()->json(['olderTransaction' => $olderTransaction]);
    }

    public function getTransactionsCountByType(Request $request)
    {
        $paymentType = $request->input('paymentType');

        $countByPayementType = Transaction::where('payment_type', $paymentType)->count();
        return response()->json(['countByPayementType' => $countByPayementType]);
    }

    public function getTransactionStatistics()
    {
        $transactionsSum = Transaction::sum('value');
        $transactionsCount = Transaction::count();

        $transactionsSumByMonth = Transaction::selectRaw('MONTH(date) as month, YEAR(date) as year, SUM(value) as sum')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $transactionsCountByMonth = Transaction::selectRaw('MONTH(date) as month, YEAR(date) as year, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $transactionByPaymentMethod = Transaction::selectRaw('payment_type, COUNT(*) as transaction_count')
            ->groupBy('payment_type')
            ->get();

        $averageTransactionAmounts = Transaction::selectRaw('MONTH(date) as month, YEAR(date) as year, AVG(value) as average_amount')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $paymentMethods = $transactionByPaymentMethod->pluck('payment_type')->toArray();
        $transactionCounts = $transactionByPaymentMethod->pluck('transaction_count')->toArray();

        return response()->json([
            'transactionsSum' => $transactionsSum,
            'transactionsCount' => $transactionsCount,
            'transactionsSumByMonth' => $transactionsSumByMonth,
            'transactionsCountByMonth' => $transactionsCountByMonth,
            'paymentMethods' => $paymentMethods,
            'transactionCounts' => $transactionCounts,
            'averageTransactionAmounts' => $averageTransactionAmounts,
        ]);
    }
}
