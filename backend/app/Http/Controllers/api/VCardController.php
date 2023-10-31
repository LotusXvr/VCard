<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VCard;
use Illuminate\Support\Facades\DB;

class VCardController extends Controller
{
    public function getVCard(VCard $vcard)
    {
        return $vcard;
    }

    public function index()
    {
        return VCard::all();
    }

    public function create(Request $request)
    {
        DB::Transaction(function () use ($request) {
            $vcard = new VCard();
            $vcard->name = $request->name;
            $vcard->email = $request->email;
            $vcard->phone_number = $request->phone_number;
            $vcard->password = $request->password;
            $vcard->confirmation_code = $request->confirmation_code;
            $vcard->blocked = false;
            $vcard->balance = 0;
            $vcard->max_debit = 5000;
            $vcard->save();
        });
    }

}
