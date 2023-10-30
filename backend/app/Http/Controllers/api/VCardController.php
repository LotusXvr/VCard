<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VCard;

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
}
