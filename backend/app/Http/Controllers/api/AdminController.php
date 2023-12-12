<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserPasswordRequest;


class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Admin::query();
        $name = $request->name;
        $orderBy = $request->orderBy;
        $orderFormat = $request->orderFormat;

        $query->when($name, function ($query, $name) {
            return $query->where('name', 'like', "%$name%");
        });

        $query->orderBy($orderBy, $orderFormat);

        $admins = $query->paginate(10);
        // não quero listar-me a mim próprio
        $admins = $admins->except(auth()->user()->id);

        return AdminResource::collection($admins);
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin $admin)
    {
        return new AdminResource($admin);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdminRequest $request)
    {
        $admin = Admin::create($request->all());
        return new AdminResource($admin);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdminRequest $request, Admin $admin)
    {
        $admin->fill($request->all());
        $admin->save();
        return new AdminResource($admin);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        // se eu tentar apagar-me a mim próprio, não deixo
        if ($admin->id == auth()->user()->id) {
            return response()->json(['message' => 'Não pode apagar o seu próprio utilizador'], 403);
        }
        $admin->delete();
        return new AdminResource($admin);
    }

    public function update_password(UpdateUserPasswordRequest $request, Admin $admin)
    {
        $admin->password = bcrypt($request->validated()['password']);
        $admin->save();
        return new AdminResource($admin);
    }

}