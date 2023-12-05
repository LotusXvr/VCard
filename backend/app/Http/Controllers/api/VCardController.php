<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateVCardRequest;
use App\Models\Category;
use App\Models\Default_Category;
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
        //$vcard->phone_number = $dataToSave['phone_number']; acho que esta a mais

        $defaultCategories = Default_Category::all();

        $defaultCategories->each(function ($defaultCategory) use ($dataToSave) {
            $defaultCategory->vcard = $dataToSave['phone_number'];
            unset($defaultCategory->id);
        });

        Category::insert($defaultCategories->toArray());
        return new VCardResource($vcard);
    }

    public function changeStatus(VCard $vcard)
    {
        try {
            // Sua lógica para alterar o status do VCard aqui
            $vcard->blocked = !$vcard->blocked; // Inverte o status

            // Você pode querer adicionar outras verificações ou lógicas aqui, dependendo das suas necessidades

            $vcard->save();

            return response()->json(['message' => 'VCard status changed successfully']);
        } catch (\Exception $e) {
            // Lida com erros, se necessário
            return response()->json(['message' => 'Error changing VCard status'], 500);
        }
    }

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

    public function getVCardStatistics()
    {
        $vcardCount = VCard::count();
        $activeVCardCount = VCard::where('blocked', 0)->count();
        $vcardBalanceSum = VCard::sum('balance');
        $activeVCardBalanceSum = VCard::where('blocked', 0)->sum('balance');

        $vcardDistribution = VCard::selectRaw('FLOOR(balance / 100) * 100 as balance_range, COUNT(*) as vcard_count')
            ->groupBy('balance_range')
            ->orderBy('balance_range')
            ->get();

        $balanceRanges = $vcardDistribution->pluck('balance_range')->toArray();
        $vcardCounts = $vcardDistribution->pluck('vcard_count')->toArray();

        return response()->json([
            'vcardCount' => $vcardCount,
            'activeVCardCount' => $activeVCardCount,
            'vcardBalanceSum' => $vcardBalanceSum,
            'activeVCardBalanceSum' => $activeVCardBalanceSum,
            'balanceRanges' => $balanceRanges,
            'vcardCounts' => $vcardCounts,
        ]);
    }
}
