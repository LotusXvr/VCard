<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
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
        return $transaction;
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
                    $transaction1->payment_type = 'VCARD';
                    $transaction1->payment_reference = $request->payment_reference;
                    $transaction1->pair_vcard = $request->payment_reference;
                    $transaction1->category_id = null;
                    $transaction1->description = null;

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
                    $transaction2->payment_type = 'VCARD';
                    $transaction2->payment_reference = $request->vcard;
                    $transaction2->pair_transaction = $transaction1->id;
                    $transaction2->pair_vcard = $request->vcard;
                    $transaction2->category_id = null;
                    $transaction2->description = null;

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

                return response()->json(['message' => 'Transação efetuada com sucesso'], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Erro ao criar transação',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
