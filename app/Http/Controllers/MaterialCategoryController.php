<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaterialCategory;
use App\Helpers\ActivityLogger;

class MaterialCategoryController extends Controller
{
    public function index()
    {
        $categories = MaterialCategory::orderBy('id', 'desc')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return redirect()->route('categories.index');
    }

    public function store(Request $request)
    {
        $request->validate(['cat_name' => 'required']);

        $cat = MaterialCategory::create([
            'cat_name' => $request->input('cat_name'),
            'date' => $request->input('date', date('Y-m-d')),
            'note' => $request->input('note', ''),
        ]);

        ActivityLogger::log('Create', "Added product category: {$cat->cat_name}");

        return redirect()->route('categories.index')->with('success', 'Product / Category added successfully.');
    }

    public function show($id)
    {
        return redirect()->route('categories.index');
    }

    public function edit($id)
    {
        $category = MaterialCategory::findOrFail($id);
        $categories = MaterialCategory::orderBy('id', 'desc')->get();
        return view('categories.index', compact('categories', 'category'));
    }

    public function update(Request $request, $id)
    {
        $cat = MaterialCategory::findOrFail($id);
        $cat->update([
            'cat_name' => $request->input('cat_name'),
            'date' => $request->input('date', $cat->date) ?? date('Y-m-d'),
            'note' => $request->input('note', ''),
        ]);

        ActivityLogger::log('Update', "Updated product category: {$cat->cat_name}");

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $cat = MaterialCategory::findOrFail($id);
        $name = $cat->cat_name;
        $cat->delete();

        ActivityLogger::log('Delete', "Deleted category: {$name}");

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
