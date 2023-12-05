<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\VCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
         * - precisa de preencher:
         * vcard, date, datetime, type, value, old_balance, new_balamce, payment_type,
         * payment_reference, pair_transaction, pair_vcard, category_id, description (nao obrigatorio)
         *
         * no caso da referencia destino ser um vcard, cria uma outra transação ligada por
         * pair_transaction e pair_vcard (null em caso contrario)
         * sabemos que o destinatário é vcard se o parametro payment_type for VCARD
         */

        // FIRST VALIDATIONS

        // verify if confirmation code is the correct one
        // having in mind the confirmation_code on the database is hashed
        $vcardOrigin = VCard::where('phone_number', $request->vcard)->first();
        if (!password_verify($request->confirmation_code, $vcardOrigin->confirmation_code)) {
            return response()->json(['message' => 'Código de confirmação inválido'], 401);
        }

        // verify if sender has enough money on account balance
        if ($vcardOrigin->balance < $request->value) {
            return response()->json(['message' => 'Saldo insuficiente'], 401);
        }

        // verify if value being sent is at least 0.01€
        if ($request->value < 0.01) {
            return response()->json(['message' => 'Valor mínimo de transferência é de 0.01€'], 401);
        }

        // verify if value being sent is less than max_debit
        if ($request->value > $vcardOrigin->max_debit) {
            return response()->json(['message' => 'Valor superior ao máximo permitido'], 401);
        }

        // VCARD

        if ($request->payment_type == 'VCARD') {
            // Verify if destination vcard exists
            $destinVCardExists = VCard::where('phone_number', $request->payment_reference)
                ->whereNull('deleted_at')
                ->first();
            if (!$destinVCardExists) {
                return response()->json(['message' => 'VCard de destino não existe'], 404);
            }

            try {
                DB::transaction(function () use ($request) {
                    // Money sending transaction
                    $transaction1 = new Transaction();
                    $transaction1->vcard = $request->vcard;
                    $transaction1->date = date('Y-m-d');
                    $transaction1->datetime = date('Y-m-d H:i:s');
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

                    // Money reception transaction
                    $transaction2 = new Transaction();
                    $transaction2->vcard = $request->payment_reference;
                    $transaction2->date = date('Y-m-d');
                    $transaction2->datetime = date('Y-m-d H:i:s');
                    $transaction2->type = 'C'; // tendo em conta esta operaçao ser a inversa, esta será de Crédito
                    $transaction2->value = $request->value;
                    $payment_referenceBalance = VCard::where('phone_number', $request->payment_reference)->first()->balance;
                    $transaction2->old_balance = $payment_referenceBalance;
                    $transaction2->new_balance = $payment_referenceBalance + $request->value;
                    $transaction2->payment_type = $request->payment_type;
                    $transaction2->payment_reference = $request->vcard;
                    $transaction2->pair_transaction = $transaction1->id;
                    $transaction2->pair_vcard = $request->vcard;
                    $transaction2->category_id = $request->category_id;
                    $transaction2->description = $request->description;

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

                });

                return response()->json(['message' => $request->value . '€ sent to ' . $request->payment_reference . ' successfully'], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Erro ao criar transação',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        // ANY OTHER PAYMENT TYPE
        elseif ($request->payment_type == 'IBAN' || $request->payment_type == 'PAYPAL' || $request->payment_type == 'VISA' || $request->payment_type == 'MB' || $request->payment_type == 'MBWAY') {
            try {
                DB::transaction(function () use ($request) {
                    // Money sending transaction
                    $transaction = new Transaction();
                    $transaction->vcard = $request->vcard;
                    $transaction->date = date('Y-m-d');
                    $transaction->datetime = date('Y-m-d H:i:s');
                    $transaction->type = 'D'; // como o utilizador está a enviar dinheiro, a primeira operação é sempre Debito
                    $transaction->value = $request->value;
                    $vcardBalance = VCard::where('phone_number', $request->vcard)->first()->balance;
                    $transaction->old_balance = $vcardBalance;
                    $transaction->new_balance = $vcardBalance - $request->value;
                    $transaction->payment_type = $request->payment_type;
                    $transaction->payment_reference = $request->payment_reference;
                    $transaction->pair_vcard = null;
                    $transaction->category_id = $request->category_id;
                    $transaction->description = $request->description;
                    $transaction->pair_transaction = null;

                    $transaction->save();

                    VCard::where('phone_number', $request->vcard)->update(['balance' => $transaction->new_balance]);
                });

                return response()->json(['message' => $request->value . '€ sent to ' . $request->payment_reference . ' successfully'], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Erro ao criar transação',
                    'error' => $e->getMessage()
                ], 500);
            }

        } else {
            return response()->json(['message' => 'Tipo de pagamento inválido'], 401);
        }
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

    // Get sum of all transactions
    public function getTransactionsSum()
    {
        $transactionsSum = Transaction::sum('value');

        return response()->json(['transactionsSum' => $transactionsSum]);
    }

    // get count of all transactions
    public function getTransactionsCount()
    {
        $transactionsCount = Transaction::count();

        return response()->json(['transactionsCount' => $transactionsCount]);
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
        ;
    }

    public function getTransactionsCountByType(Request $request)
    {
        $paymentType = $request->input('paymentType');

        $countByPayementType = Transaction::where('payment_type', $paymentType)->count();
        return response()->json(['countByPayementType' => $countByPayementType]);
    }

    // get sum of all transaction by month this year
    public function getTransactionsSumByMonth()
    {
        $transactionsSumByMonth = Transaction::select(DB::raw('MONTH(date) as month'), DB::raw('SUM(value) as sum'))
            ->whereYear('date', date('Y'))
            ->groupBy('month')
            ->get();

        return response()->json(['transactionsSumByMonth' => $transactionsSumByMonth]);
    }

    // get quantity of transaction  by month this year
    public function getTransactionsCountByMonth()
    {
        $transactionsCountByMonth = Transaction::select(DB::raw('MONTH(date) as month'), DB::raw('COUNT(*) as count'))
            ->whereYear('date', date('Y'))
            ->groupBy('month')
            ->get();

        return response()->json(['transactionsCountByMonth' => $transactionsCountByMonth]);
    }

    public function getTransactionsByPaymentMethod()
    {
        // Adjust this query based on your Transaction model and logic
        $transactionByPaymentMethod = Transaction::selectRaw('payment_type, COUNT(*) as transaction_count')
            ->groupBy('payment_type')
            ->get();

        $paymentMethods = $transactionByPaymentMethod->pluck('payment_type')->toArray();
        $transactionCounts = $transactionByPaymentMethod->pluck('transaction_count')->toArray();

        return response()->json([
            'paymentMethods' => $paymentMethods,
            'transactionCounts' => $transactionCounts,
        ]);
    }

    public function getAverageTransactionAmountByMonth()
    {
        $averageTransactionAmounts = Transaction::selectRaw('MONTH(date) as month, YEAR(date) as year, AVG(value) as average_amount')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json($averageTransactionAmounts);
    }
}
