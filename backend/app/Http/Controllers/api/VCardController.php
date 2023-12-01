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
use App\Services\Base64Services;


class VCardController extends Controller
{

    public function index()
    {
        $vcards = VCard::all();
    }

    public function show(VCard $vcard)
    {
        return new VCardResource($vcard);
    }

    // Register a new vcard
    public function store(CreateVCardRequest $request)
    {
        $dataToSave = $request->validated();
        $dataToSave['blocked'] = 0; // Set blocked to 0
        $dataToSave['max_debit'] = 5000;
        $dataToSave['balance'] = 0;

        $base64ImagePhoto = array_key_exists("base64ImagePhoto", $dataToSave) ?
            $dataToSave["base64ImagePhoto"] : ($dataToSave["base64ImagePhoto"] ?? null);
        unset($dataToSave["base64ImagePhoto"]);

        $vcard = new VCard();
        $vcard->name = $dataToSave['name'];
        $vcard->email = $dataToSave['email'];

        $vcard->phone_number = $dataToSave['phone_number'];

        $vcard->password = bcrypt($dataToSave['password']);
        $vcard->blocked = $dataToSave['blocked'];
        $vcard->max_debit = $dataToSave['max_debit'];
        $vcard->balance = $dataToSave['balance'];
        $vcard->confirmation_code = bcrypt($dataToSave['confirmation_code']);

        if ($base64ImagePhoto) {
            $vcard->photo_url = $this->storeBase64Image($base64ImagePhoto);
        }


        $vcard->save();
        $vcard->phone_number = $dataToSave['phone_number'];
        return new VCardResource($vcard);
    }

    // Store base64 image
    private function storeBase64AsFile(VCard $vCard, string $base64String)
    {
        $targetDir = storage_path('app/public/fotos');
        $newfilename = $vCard->phone_number . "_" . rand(1000, 9999);
        $base64Service = new Base64Services();
        return $base64Service->saveFile($base64String, $targetDir, $newfilename);
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
