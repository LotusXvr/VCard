<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Default_Category;
use App\Models\VCard;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateCategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Category::all();
    }

    public function show(Category $category) {
        return $category;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUpdateCategoryRequest $request)
    {
        $newCategory = Category::create($request->except('id'));
        return $newCategory;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreUpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());
        return $category;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $hasTransactions = Transaction::where('category_id', $category->id)->exists();

        if ($hasTransactions) {
            $category->delete();
        } else {
            $category->forceDelete();
        }

        return $category;
    }
}
