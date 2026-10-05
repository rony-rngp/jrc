<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Helpers\ActivityLogger;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::orderBy('id', 'desc')->get();
        return view('payment_methods.index', compact('methods'));
    }

    public function store(Request $request)
    {
        $request->validate(['method_name' => 'required']);

        $pm = PaymentMethod::create([
            'method_name' => $request->input('method_name'),
            'account_type' => $request->input('account_type', 'Cash'),
            'amount' => (float)$request->input('amount', 0),
            'date' => $request->input('date', date('Y-m-d')),
            'note' => $request->input('note', ''),
        ]);

        ActivityLogger::log('Create', "Added payment method: {$pm->method_name}");

        return redirect()->route('payment-methods.index')->with('success', 'Payment Method added successfully.');
    }

    public function update(Request $request, $id)
    {
        $pm = PaymentMethod::findOrFail($id);
        $pm->update([
            'method_name' => $request->input('method_name'),
            'account_type' => $request->input('account_type', $pm->account_type),
            'amount' => (float)$request->input('amount', $pm->amount),
            'note' => $request->input('note', $pm->note),
        ]);

        ActivityLogger::log('Update', "Updated payment method: {$pm->method_name}");

        return redirect()->route('payment-methods.index')->with('success', 'Payment Method updated successfully.');
    }

    public function destroy($id)
    {
        $pm = PaymentMethod::findOrFail($id);
        $name = $pm->method_name;
        $pm->delete();

        ActivityLogger::log('Delete', "Deleted payment method: {$name}");

        return redirect()->route('payment-methods.index')->with('success', 'Payment Method deleted successfully.');
    }
}
