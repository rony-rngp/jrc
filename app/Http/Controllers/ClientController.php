<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\PaymentReceivedProject;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::orderBy('id', 'desc')->get();
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return redirect()->route('clients.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required',
        ]);

        $company_name = $request->input('company_name');
        $person_name = $request->input('persion_name', '');
        $contact = $request->input('contact', '');
        $address = $request->input('address', '');
        $date = $request->input('date', date('Y-m-d'));
        $note = $request->input('note', '');
        $opening_balance = (float)$request->input('amount', 0);

        $client = Client::create([
            'company_name' => $company_name,
            'persion_name' => $person_name,
            'contact' => $contact,
            'address' => $address,
            'date' => $date,
            'note' => $note,
            'opening_balance' => $opening_balance,
        ]);

        if ($opening_balance > 0) {
            PaymentReceivedProject::create([
                'company_name' => $company_name,
                'Source' => 'Received',
                'fund_recev' => 'Project',
                'name' => '',
                'method_name' => 'CASH',
                'bill_amount' => $opening_balance,
                'pay_amount' => 0,
                'type' => 'Bill',
                'date' => $date,
                'note' => 'Opening Balance',
                'val' => '1',
            ]);
        }

        ActivityLogger::log('Create', "Added client: {$company_name}");

        return redirect()->route('clients.index')->with('success', 'Client added successfully.');
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $client->update([
            'company_name' => $request->input('company_name', $client->company_name),
            'persion_name' => $request->input('persion_name', $client->persion_name) ?? '',
            'contact' => $request->input('contact', $client->contact) ?? '',
            'address' => $request->input('address', $client->address) ?? '',
            'opening_balance' => (float)$request->input('opening_balance', $client->opening_balance ?? 0),
            'date' => $request->input('date', $client->date) ?? date('Y-m-d'),
            'note' => $request->input('note', $client->note) ?? '',
        ]);

        ActivityLogger::log('Update', "Updated client: {$client->company_name}");

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $name = $client->company_name;
        $client->delete();

        ActivityLogger::log('Delete', "Deleted client: {$name}");

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
