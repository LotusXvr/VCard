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
         */



        try {
            DB::transaction(function () use ($request) {
                $isDestinVCard = VCard::where('phone_number', $request->vcardDestination)
                    ->whereNull('deleted_at')
                    ->first();
                if ($isDestinVCard) {

                    // Transação de envio
                    $transaction1 = new Transaction();
                    $transaction1->vcard = $request->vcardOrigin;
                    $transaction1->date = date('Y-m-d');
                    $transaction1->datetime = date('Y-m-d H:i:s');
                    $transaction1->type = 'D'; // como o utilizador está a enviar dinheiro, a primeira operação é sempre Debito
                    $transaction1->value = $request->value;
                    $vcardOriginBalance = VCard::where('phone_number', $request->vcardOrigin)->first()->balance;
                    $transaction1->old_balance = $vcardOriginBalance;
                    $transaction1->new_balance = $vcardOriginBalance - $request->value;
                    $transaction1->payment_type = 'VCARD';
                    $transaction1->payment_reference = $request->vcardDestination;
                    $transaction1->pair_vcard = $request->vcardDestination;
                    $transaction1->category_id = null;
                    $transaction1->description = null;

                    // Transação de receção
                    $transaction2 = new Transaction();
                    $transaction2->vcard = $request->vcardDestination;
                    $transaction2->date = date('Y-m-d');
                    $transaction2->datetime = date('Y-m-d H:i:s');
                    $transaction2->type = 'C'; // tendo em conta esta operaçao ser a inversa, esta será de Crédito
                    $transaction2->value = $request->value;
                    $vcardDestinationBalance = VCard::where('phone_number', $request->vcardDestination)->first()->balance;
                    $transaction2->old_balance = $vcardDestinationBalance;
                    $transaction2->new_balance = $vcardDestinationBalance + $request->value;
                    $transaction2->payment_type = 'VCARD';
                    $transaction2->payment_reference = $request->vcardOrigin;
                    $transaction2->pair_transaction = $transaction1->id;
                    $transaction2->pair_vcard = $request->vcardOrigin;
                    $transaction2->category_id = null;
                    $transaction2->description = null;

                    $transaction1->pair_transaction = $transaction2->id;
                    $transaction2->pair_transaction = $transaction1->id;


                    $transaction1->save();
                    $transaction2->save();

                }

            });



            return response()->json(['message' => 'Transação efetuada com sucesso'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar transação',
                'error' => $e->getMessage()
            ], 500);
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
