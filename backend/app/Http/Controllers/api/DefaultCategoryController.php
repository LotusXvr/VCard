<?php

namespace App\Http\Controllers\api;

use App\Models\Default_Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateDefaultCategoryRequest;
use App\Models\Transaction;

class DefaultCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Default_Category::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUpdateDefaultCategoryRequest $request)
    {
        $default_Category = Default_Category::where('name', $request->name)->first();

        if ($default_Category) {
            return response()->json([
                'message' => 'Category name already exists',
            ], 422);
        }

        $default_Category = Default_Category::onlyTrashed()->where('name', $request->name)->first();
        if($default_Category) {
            $default_Category->restore();
            return $default_Category;
        }
        $newCategory = Default_Category::create($request->except('id'));
        return $newCategory;
    }

    /**
     * Display the specified resource.
     */

    public function show(Default_Category $default_category)
    {
        return $default_category;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreUpdateDefaultCategoryRequest $request, Default_Category $default_category)
    {
        $default_category->update($request->validated());
        return $default_category;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Default_Category $default_category)
    {
        $hasTransactions = Transaction::where('category_id', $default_category->id)->exists();

        if ($hasTransactions) {
            $default_category->delete();
        } else {
            $default_category->forceDelete();
        }

        return $default_category;
    }
}
