<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Party;
use App\Helpers\ActivityLogger;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Party::orderBy('id', 'desc')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'party_name' => 'required',
        ]);

        $party_name = $request->input('party_name');
        $party_phone = $request->input('party_phone', '');
        $party_type = $request->input('party_type', '');
        $address = $request->input('address', '');
        $amount = (float)$request->input('amount', 0);
        $date = $request->input('date', date('Y-m-d'));
        $note = $request->input('note', '');

        Party::create([
            'party_name' => $party_name,
            'party_phone' => $party_phone,
            'party_type' => $party_type,
            'address' => $address,
            'amount' => $amount,
            'balance_type' => '',
            'date' => $date,
            'note' => $note,
        ]);

        ActivityLogger::log('Create', "Added supplier: {$party_name}");

        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully.');
    }

    public function edit($id)
    {
        $supplier = Party::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Party::findOrFail($id);
        $supplier->update([
            'party_name' => $request->input('party_name', $supplier->party_name),
            'party_phone' => $request->input('party_phone', $supplier->party_phone) ?? '',
            'party_type' => $request->input('party_type', $supplier->party_type) ?? '',
            'address' => $request->input('address', $supplier->address) ?? '',
            'amount' => $request->input('amount', $supplier->amount) ?? 0,
            'date' => $request->input('date', $supplier->date) ?? date('Y-m-d'),
            'note' => $request->input('note', $supplier->note) ?? '',
        ]);

        ActivityLogger::log('Update', "Updated supplier: {$supplier->party_name}");

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy($id)
    {
        $supplier = Party::findOrFail($id);
        $name = $supplier->party_name;
        $supplier->delete();

        ActivityLogger::log('Delete', "Deleted supplier: {$name}");

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
