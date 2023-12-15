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
use App\Http\Requests\UpdateVcardCodeRequest;
use App\Models\MoneyRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class VCardController extends Controller
{

    public function index(Request $request)
    {

        $query = VCard::query();

        // Retrieve filters from the request
        $name = $request->name;
        $blocked = $request->blocked;
        $orderBy = $request->orderBy;
        $orderFormat = $request->orderFormat;

        // Apply filters conditionally
        $query->when($name, function ($query, $name) {
            return $query->where('name', 'like', "%$name%");
        });

        // if blocked is not null, then we filter by blocked status
        if ($blocked !== null) {
            $query->where('blocked', $blocked);
        }

        $query->orderBy($orderBy, $orderFormat);

        // Get paginated results
        $filteredVCards = $query->paginate(10);

        return response()->json($filteredVCards);
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

        $defaultCategories = Default_Category::all();

        $defaultCategories->each(function ($defaultCategory) use ($dataToSave) {
            $defaultCategory->vcard = $dataToSave['phone_number'];
            unset($defaultCategory->id);
        });

        Category::insert($defaultCategories->toArray());
        return new VCardResource($vcard);
    }

    public function dismissVCard(Request $request, VCard $vcard)
    {
        if (!$request->confirmation_code || !$request->password) {
            return response()->json(['message' => 'Confirmation Code and Password are required'], 422);
        }

        if (!Hash::check($request->confirmation_code, $vcard->confirmation_code)) {
            return response()->json(['message' => 'Confirmation Code is incorrect'], 422);
        }

        if (!Hash::check($request->password, $vcard->password)) {
            return response()->json(['message' => 'Password is incorrect'], 422);
        }

        if ($vcard->balance > 0) {
            return response()->json(['message' => 'VCard balance must be 0 to be dismissed'], 422);
        }

        // Delete photo file from storage
        if ($vcard->photo_url) {
            if (Storage::exists('public/fotos/' . $vcard->photo_url)) {
                Storage::delete('public/fotos/' . $vcard->photo_url);
            }
        }

        // se tiver transações fazemos um soft delete, se não tiver fazemos um hard delete
        if ($vcard->transactions()->count() > 0) {

            Transaction::where('vcard', $vcard->phone_number)->delete();

            // delete vcard
            $vcard->delete();
        } else {
            // delete categories
            // foreach ($vcard->categories() as $category) {
            //     $category->forceDelete();
            // }
            Category::where('vcard', $vcard->phone_number)->forceDelete();

            // delete vcard
            $vcard->forceDelete();
        }

        return response()->json(['message' => 'VCard dismissed successfully']);
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

    public function update_confirmation_code(UpdateVcardCodeRequest $request, VCard $vcard)
    {
        //$vcard = VCard::where('phone_number', $request->phone_number)->first();
        if (Hash::check($request->validated()['current_confirmation_code'], $vcard->confirmation_code)) {
            $vcard->confirmation_code = bcrypt($request->validated()['confirmation_code']);
        } else {
            return response()->json(['message' => 'Current confirmation code is incorrect'], 422);
        }


        $vcard->save();
        return new VCardResource($vcard);
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
        if ($vcard->balance > 0) {
            return response()->json(['message' => 'VCard balance must be 0 to be dismissed'], 422);
        }

        // Delete photo file from storage
        if ($vcard->photo_url) {
            if (Storage::exists('public/fotos/' . $vcard->photo_url)) {
                Storage::delete('public/fotos/' . $vcard->photo_url);
            }
        }

        // se tiver transações fazemos um soft delete, se não tiver fazemos um hard delete
        if ($vcard->transactions()->count() > 0) {

            Transaction::where('vcard', $vcard->phone_number)->delete();

            // delete vcard
            $vcard->delete();
        } else {
            // delete categories
            // foreach ($vcard->categories() as $category) {
            //     $category->forceDelete();
            // }
            Category::where('vcard', $vcard->phone_number)->forceDelete();

            // delete vcard
            $vcard->forceDelete();
        }

        return response()->json(['message' => 'VCard dismissed successfully']);
    }

    public function isPhoneNumberAlreadyUsed(Request $request)
    {
        $existingVCard = VCard::where('phone_number', $request->phone)->first();

        if ($existingVCard) {
            return response()->json(['message' => 'Phone number is already in use'], 422);
        }

        return response()->json(['message' => 'Phone number is available']);
    }

    public function getTransactionsByPhoneNumber(Request $request, VCard $vcard)
    {
        $phoneNumber = $vcard->phone_number;

        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $type = $request->type;
        $method = $request->method;
        $category = $request->category;
        $orderBy = $request->orderBy;

        $query = Transaction::where('vcard', $phoneNumber);

        $query->when($startDate, function ($query, $startDate) {
            return $query->where('date', '>=', $startDate);
        });

        $query->when($endDate, function ($query, $endDate) {
            return $query->where('date', '<=', $endDate);
        });

        $query->when($type, function ($query, $type) {
            return $query->where('type', $type);
        });

        $query->when($method, function ($query, $method) {
            return $query->where('payment_type', $method);
        });
        $query->when($category, function ($query, $category) {
            return $query->where('category_id', $category);
        });

        $transactions = $query->orderBy('datetime', $orderBy)->paginate(20);  // Utiliza paginate em vez de get

        return response()->json($transactions);
    }

    public function getMoneyRequestsByPhoneNumber(VCard $vcard)
    {
        $moneyRequests = MoneyRequest::where('to_vcard', $vcard->phone_number)->orderBy('created_at', 'DESC')->get();

        return response()->json($moneyRequests);
    }

    public function getPendingMoneyRequestsByPhoneNumber(VCard $vcard)
    {
        $moneyRequests = MoneyRequest::where('from_vcard', $vcard->phone_number)->where('status', null)->orderBy('created_at', 'DESC')->get();

        return response()->json($moneyRequests);
    }

    public function getLastMonthTransactionsByPhoneNumber(VCard $vcard)
    {
        $phoneNumber = $vcard->phone_number;

        $lastMonthTransactions = Transaction::where('vcard', $phoneNumber)
            ->whereYear('datetime', '=', now()->year)
            ->whereMonth('datetime', '=', now()->month)
            ->orderBy('datetime', 'desc')
            ->get();

        return response()->json($lastMonthTransactions);
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

    public function getCategoryFromVCardWithTrashed(VCard $vcard)
    {
        return Category::withTrashed()->where('vcard', $vcard->phone_number)->get();
    }

    public function deleteCategoryFromVCard(VCard $vcard, Request $request)
    {
        $category = Category::where('vcard', $vcard->phone_number)->where('category_id', $request->category_id)->first();

        if ($category) {
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully']);
        } else {
            return response()->json(['message' => 'Category not found'], 404);
        }
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

    public function reforcarPoupanca(Request $request, VCard $vcard)
    {
        if ($vcard->balance >= $request->valor) {
            $vcard->balance -= $request->valor;
            $vcard->savings += $request->valor;

            $vcard->save();

            return new VCardResource($vcard);
        }

        if ($request->valor <= 0) {
            return response()->json(['message' => 'Invalid value'], 422);
        }
        return response()->json(['message' => 'Not enough balance to reinforce savings'], 422);


    }


    public function retirarPoupanca(Request $request, VCard $vcard)
    {
        if ($vcard->savings >= $request->valor) {
            $vcard->balance += $request->valor;
            $vcard->savings -= $request->valor;

            $vcard->save();

            return new VCardResource($vcard);
        }

        if ($request->valor <= 0) {
            return response()->json(['message' => 'Invalid value.'], 422);
        }
        return response()->json(['message' => 'Not enough savings to withdraw from.'], 422);
    }


    public function updateSpins(Request $request, $phone_number)
    {
        try {
            $vcard = VCard::where('phone_number', $phone_number)->firstOrFail();

            // Update the spins value
            $vcard->spins = $request->input('spins');
            $vcard->save();

            return response()->json(['message' => 'Spins updated successfully', 'data' => $vcard], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating spins', 'error' => $e->getMessage()], 500);
        }
    }
}
