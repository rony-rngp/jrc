<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountHead;
use App\Helpers\ActivityLogger;

class AccountHeadController extends Controller
{
    public function index()
    {
        $allHeads = AccountHead::orderBy('head_name', 'asc')->get();
        $hierarchicalHeads = $this->buildHierarchy($allHeads);

        return view('account_heads.index', [
            'heads' => $hierarchicalHeads,
            'allHeads' => $allHeads
        ]);
    }

    public function create()
    {
        return redirect()->route('account-heads.index');
    }

    private function buildHierarchy($heads, $parentId = 0, $level = 0)
    {
        $result = [];
        $children = $heads->where('parent_id', $parentId)->sortBy('head_name');

        foreach ($children as $head) {
            $head->level = $level;
            $result[] = $head;
            $subResults = $this->buildHierarchy($heads, $head->id, $level + 1);
            foreach ($subResults as $sub) {
                $result[] = $sub;
            }
        }

        return $result;
    }

    public function store(Request $request)
    {
        $request->validate(['head_name' => 'required']);

        $head = AccountHead::create([
            'head_name' => $request->input('head_name'),
            'head_type' => $request->input('head_type', 'Payment'),
            'parent_id' => (int)$request->input('parent_id', 0),
            'description' => $request->input('description', ''),
        ]);

        ActivityLogger::log('Create', "Added account head: {$head->head_name}");

        return redirect()->route('account-heads.index')->with('success', 'Account Head added successfully.');
    }

    public function show($id)
    {
        return redirect()->route('account-heads.index');
    }

    public function edit($id)
    {
        $head = AccountHead::findOrFail($id);
        $allHeads = AccountHead::orderBy('head_name', 'asc')->get();
        $heads = $this->buildHierarchy($allHeads);
        return view('account_heads.index', compact('heads', 'allHeads', 'head'));
    }

    public function update(Request $request, $id)
    {
        $head = AccountHead::findOrFail($id);
        $head->update([
            'head_name' => $request->input('head_name'),
            'head_type' => $request->input('head_type', $head->head_type),
            'parent_id' => (int)$request->input('parent_id', $head->parent_id ?? 0),
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
