<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateVCardRequest;
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

    public function store(CreateVCardRequest $request)
    {
        $vcard = VCard::create($request->all());
        return new VCardResource($vcard);
    }

}
