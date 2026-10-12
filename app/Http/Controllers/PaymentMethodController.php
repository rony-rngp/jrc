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

    public function create()
    {
        return redirect()->route('payment-methods.index');
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

    public function show($id)
    {
        $method = PaymentMethod::findOrFail($id);

        $generalTx = \App\Models\GeneralTransaction::where('method_id', $method->id)->orderBy('t_date', 'desc')->get();
        $clientPayments = \App\Models\PaymentReceivedProject::where('method_id', $method->id)->where('type', 'Receive')->orderBy('date', 'desc')->get();
        $supplierPayments = \App\Models\SupplierPayment::where('method_id', $method->id)->orderBy('t_date', 'desc')->get();
        $projectExpenses = \App\Models\ProjectExpense::where('method_id', $method->id)->orderBy('t_date', 'desc')->get();

        $totalInflow = (float)$generalTx->where('t_type', 'Receive')->sum('amount') + (float)$clientPayments->sum('pay_amount');
        $totalOutflow = (float)$generalTx->where('t_type', 'Payment')->sum('amount') + (float)$supplierPayments->sum('amount') + (float)$projectExpenses->sum('amount');

        return view('payment_methods.show', compact('method', 'generalTx', 'clientPayments', 'supplierPayments', 'projectExpenses', 'totalInflow', 'totalOutflow'));
    }

    public function edit($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $methods = PaymentMethod::orderBy('id', 'desc')->get();
        return view('payment_methods.index', compact('methods', 'method'));
    }

    public function update(Request $request, $id)
    {
        $pm = PaymentMethod::findOrFail($id);
        $pm->update([
            'method_name' => $request->input('method_name'),
            'account_type' => $request->input('account_type', $pm->account_type),
            'amount' => (float)$request->input('amount', $pm->amount),
            'date' => $request->input('date', $pm->date) ?? date('Y-m-d'),
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
