<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateVCardRequest;
use App\Http\Resources\VCardResource;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Models\VCard;
use Illuminate\Support\Facades\DB;

class VCardController extends Controller
{

    public function index()
    {
        return VCard::all();
    }

    public function show(VCard $vcard)
    {
        return new VCardResource($vcard);
    }

    public function store(CreateVCardRequest $request)
    {
        $data = $request->validated();
        $data['blocked'] = 0; // Set blocked to 0
        $data['max_debit'] = 5000;
        $data['balance'] = 0;
        $vcard = VCard::create($data);
        return new VCardResource($vcard);
    }


    public function update(CreateVCardRequest $request, VCard $vcard)
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


}
