<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VCardResource;
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

    public function store(Request $request)
    {
        $vcard = VCard::create($request->all());
        return new VCardResource($vcard);
    }

}
