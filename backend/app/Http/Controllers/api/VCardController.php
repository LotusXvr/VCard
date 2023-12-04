<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateVCardRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\VCard;
use App\Http\Resources\VCardResource;
use App\Http\Requests\UpdateVCardRequest;
use Illuminate\Support\Facades\Storage;
use App\Services\Base64Services;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Http\Requests\UpdateUserPasswordRequest;



class VCardController extends Controller
{

    public function index()
    {
        return VCard::paginate(10);
    }

    public function show(VCard $vcard)
    {
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

    // Register a new vcard
    public function store(CreateVCardRequest $request)
    {
        $dataToSave = $request->validated();

        $base64ImagePhoto = array_key_exists("base64ImagePhoto", $dataToSave) ?
            $dataToSave["base64ImagePhoto"] : ($dataToSave["base64ImagePhoto"] ?? null);
        unset($dataToSave["base64ImagePhoto"]);

        $vcard = new VCard();
        $vcard->phone_number = $dataToSave['phone_number'];
        $vcard->name = $dataToSave['name'];
        $vcard->email = $dataToSave['email'];
        $vcard->password = bcrypt($dataToSave['password']);
        $vcard->confirmation_code = bcrypt($dataToSave['confirmation_code']);

        $vcard->blocked = 0;
        $vcard->max_debit = 5000;
        $vcard->balance = 0;

        // Create a new photo file from base64 content
        if ($base64ImagePhoto) {
            $vcard->photo_url = $this->storeBase64AsFile($vcard, $base64ImagePhoto);
        }

        $vcard->save();
        $vcard->phone_number = $dataToSave['phone_number'];
        return new VCardResource($vcard);
    }

    /* public function update(Request $request, VCard $vcard)
    {
        $vcard->fill($request->all());
        $vcard->save();
        return new VCardResource($vcard);
    } */

    /*
        TENTAR FAZER O UPDATE COM O REQUEST VALIDATED
    */
    public function update(UpdateVCardRequest $request, VCard $vcard)
    {
        $dataToSave = $request->validated();

        $base64ImagePhoto = array_key_exists("base64ImagePhoto", $dataToSave) ?
            $dataToSave["base64ImagePhoto"] : ($dataToSave["base64ImagePhoto"] ?? null);
        $deletePhotoOnServer = array_key_exists("deletePhotoOnServer", $dataToSave) && $dataToSave["deletePhotoOnServer"];
        unset($dataToSave["base64ImagePhoto"]);
        unset($dataToSave["deletePhotoOnServer"]);

        $vcard->fill($dataToSave);

        if ($vcard->photo_url && ($deletePhotoOnServer || $base64ImagePhoto)) {
            if (Storage::exists('public/fotos/' . $vcard->photo_url)) {
                Storage::delete('public/fotos/' . $vcard->photo_url);
            }
            $vcard->photo_url = null;
        }

        if ($base64ImagePhoto) {
            $vcard->photo_url = $this->storeBase64AsFile($vcard, $base64ImagePhoto);
        }
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

    public function update_password(UpdateUserPasswordRequest $request, VCard $vcard)
    {
        $vcard->password = bcrypt($request->validated()['password']);
        $vcard->save();
        return new VCardResource($vcard);
    }

    public function getCategoryFromVCard(VCard $vcard)
    {
        return Category::where('vcard', $vcard->phone_number)->get();
    }

}
