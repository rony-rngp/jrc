<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountHead;
use App\Helpers\ActivityLogger;

class AccountHeadController extends Controller
{
    public function index()
    {
        $heads = AccountHead::orderBy('id', 'desc')->get();
        return view('account_heads.index', compact('heads'));
    }

    public function store(Request $request)
    {
        $request->validate(['head_name' => 'required']);

        $head = AccountHead::create([
            'head_name' => $request->input('head_name'),
            'head_type' => $request->input('head_type', 'Expense'),
            'parent_id' => (int)$request->input('parent_id', 0),
            'description' => $request->input('description', ''),
        ]);

        ActivityLogger::log('Create', "Added account head: {$head->head_name}");

        return redirect()->route('account-heads.index')->with('success', 'Account Head added successfully.');
    }

    public function update(Request $request, $id)
    {
        $head = AccountHead::findOrFail($id);
        $head->update([
            'head_name' => $request->input('head_name'),
            'head_type' => $request->input('head_type', $head->head_type),
            'description' => $request->input('description', $head->description),
        ]);

        ActivityLogger::log('Update', "Updated account head: {$head->head_name}");

        return redirect()->route('account-heads.index')->with('success', 'Account Head updated successfully.');
    }

    public function destroy($id)
    {
        $head = AccountHead::findOrFail($id);
        $name = $head->head_name;
        $head->delete();

        ActivityLogger::log('Delete', "Deleted account head: {$name}");

        return redirect()->route('account-heads.index')->with('success', 'Account Head deleted successfully.');
    }
}
