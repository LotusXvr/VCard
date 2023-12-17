<?php

namespace App\Http\Controllers\api;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Notification::all();
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        return $notification;
    }

    public function store(Request $request)
    {
        $data = $request->only(['vcard', 'message']);

        $notification = Notification::create($data);

        return $notification;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();

        return $notification;
    }

    public function getNotificationsByPhoneNumber(Request $request){

    }
}
