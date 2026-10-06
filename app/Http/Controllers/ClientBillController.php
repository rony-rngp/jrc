<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesGenerate;
use App\Models\Client;
use App\Models\Project;
use App\Models\PaymentMethod;
use App\Models\SupplierBill;
use App\Models\GeneralTransaction;
use App\Models\ProjectExpense;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;

class ClientBillController extends Controller
{
    public function index(Request $request)
    {
        // Handle AJAX Requests
        if ($request->has('action') || $request->ajax()) {
            $action = $request->input('action');

            // 1. Get Projects by Client
            if ($action == 'get_projects_by_client') {
                $clientName = $request->input('client_name');
                $projects = Project::where('company_name', $clientName)->orderBy('project_name', 'asc')->get();

                $html = '<option value="">-- Choose a Project --</option>';
                foreach ($projects as $p) {
                    $html .= "<option value='{$p->id}'>{$p->project_name}</option>";
                }

                return response()->json(['status' => 'success', 'html' => $html]);
            }

            // 2. Get Project Data & Cost Breakdown
            if ($action == 'get_project_data') {
                $projectId = (int)$request->input('project_id');
                $project = Project::find($projectId);

                if (!$project) {
                    return response()->json(['status' => 'error']);
                }

                $projectName = $project->project_name;
                $companyName = $project->company_name;
                $totalCost = 0;

                $html = '<div style="background:#fcfcfc; border:1px solid #eee; border-radius:5px; padding:10px; margin-bottom:15px;">';
                $html .= '<h5 style="margin-top:0; color:#3498db; font-weight:bold; font-size:14px; border-bottom:1px solid #ddd; padding-bottom:8px;"><i class="fa fa-list-alt"></i> Detailed Cost Breakdown</h5>';
                $html .= '<table class="table table-condensed table-striped" style="font-size:12px; margin-bottom:0;">';
                $html .= '<thead><tr><th width="20%">Type</th><th width="60%">Details & Purpose</th><th width="20%" class="text-right">Amount</th></tr></thead><tbody>';

                // 1. Supplier Bills
                $bills = DB::table('supplier_bills as sb')
                    ->where('sb.project_id', $projectId)
                    ->select('sb.id', 'sb.voucher_no', 'sb.grand_total', 'sb.note')
                    ->get();

                foreach ($bills as $b) {
                    $products = DB::table('supplier_bill_items as sbi')
                        ->leftJoin('material_category as mc', 'mc.id', '=', 'sbi.product_id')
                        ->where('sbi.bill_id', $b->id)
                        ->pluck('mc.cat_name')
                        ->filter()
                        ->implode(', ');

                    $itemsText = !empty($products) ? "<br><span style='color:#2980b9; font-weight:600;'><i class='fa fa-cubes'></i> Items: {$products}</span>" : "";
                    $noteText = !empty($b->note) ? "<br><small class='text-muted'>Note: {$b->note}</small>" : "";
                    $html .= "<tr><td><span class='label bg-yellow' style='color:#333 !important;'>Material</span></td><td><b style='color:#333;'>Voucher: {$b->voucher_no}</b>{$itemsText}{$noteText}</td><td class='text-right' style='font-weight:bold;'>".number_format((float)$b->grand_total, 2)."</td></tr>";
                    $totalCost += (float)$b->grand_total;
                }

                // 2. Direct Project Expenses
                $dirExpenses = DB::table('project_expenses')->where('project_id', $projectId)->get();
                foreach ($dirExpenses as $pe) {
                    $noteText = !empty($pe->note) ? "<br><small class='text-muted'>Note: {$pe->note}</small>" : "";
                    $html .= "<tr><td><span class='label bg-red'>Direct Exp.</span></td><td><b style='color:#333;'>Head: {$pe->expense_head}</b> <small>(Vch: {$pe->voucher_no})</small>{$noteText}</td><td class='text-right' style='font-weight:bold;'>".number_format((float)$pe->amount, 2)."</td></tr>";
                    $totalCost += (float)$pe->amount;
                }

                // 3. General Transactions
                $genTx = GeneralTransaction::where('project_id', $projectId)
                    ->where('t_type', 'Payment')
                    ->where('note', 'not like', '%Purchase%')
                    ->where('note', 'not like', '%Cash Purchase%')
                    ->get();

                foreach ($genTx as $gt) {
                    $reasonText = $gt->note;
                    if (str_contains($reasonText, 'Reason:')) {
                        $parts = explode('Reason:', $reasonText);
                        $reasonText = trim($parts[1] ?? '');
                    }
                    $html .= "<tr><td><span class='label bg-red'>Gen. Exp.</span></td><td><b style='color:#333;'>Method: {$gt->payment_category}</b> <small>(Vch: {$gt->check_no})</small><br><span style='color:#e74c3c; font-weight:600;'><i class='fa fa-tag'></i> Reason: {$reasonText}</span></td><td class='text-right' style='font-weight:bold;'>".number_format((float)$gt->amount, 2)."</td></tr>";
                    $totalCost += (float)$gt->amount;
                }

                if ($totalCost == 0) {
                    $html .= "<tr><td colspan='3' class='text-center text-muted' style='padding:15px;'>No materials or expenses found.</td></tr>";
                }
                $html .= '</tbody></table>';
                $html .= '<div style="text-align:right; font-weight:bold; font-size:15px; color:#c0392b; margin-top:10px; border-top:2px solid #ddd; padding-top:8px;">Total Calculated Cost: '.number_format($totalCost, 2).'</div>';
                $html .= '</div>';

                $finalExists = SalesGenerate::where('project_name', $projectName)->where('note', 'like', '%[Final Bill]%')->exists();

                return response()->json([
                    'status' => 'success',
                    'project_name' => $projectName,
                    'company_name' => $companyName,
                    'actual_cost' => $totalCost,
                    'breakdown_html' => $html,
                    'final_exists' => $finalExists
                ]);
            }
        }

        $bills = SalesGenerate::orderBy('id', 'desc')->get();
        $clients = Client::orderBy('company_name', 'asc')->get();

        return view('client_bills.index', compact('bills', 'clients'));
    }

    public function create()
    {
        $clients = Client::orderBy('company_name', 'asc')->get();
        $projects = Project::orderBy('project_name', 'asc')->get();
        $paymentMethods = PaymentMethod::orderBy('method_name', 'asc')->get();
        return view('client_bills.create', compact('clients', 'projects', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        if ($request->has('action') || $request->has('ajax_request') || $request->ajax()) {
            return $this->index($request);
        }

        $projectName = $request->input('project_name');
        $companyName = $request->input('company_name');
        $actualCost = (float)$request->input('actual_cost', 0);
        $billAmount = (float)$request->input('bill_amount', 0);
        $date = $request->input('date', date('Y-m-d'));
        $billStage = $request->input('bill_stage', 'Running Bill');
        $note = $request->input('note', '');
        $discount = (float)$request->input('discount', 0);
        $netAmount = max(0, $billAmount - $discount);

        if ($actualCost <= 0 && $billAmount > 0) {
            $actualCost = $billAmount;
        }

        $editId = $request->input('edit_id');

        if ($actualCost <= 0 && $billAmount <= 0 && empty($editId)) {
            return redirect()->route('client-bills.index')->with('error', 'Access Denied: Cannot generate a bill for a project with zero actual cost. Please record expenses first.');
        }

        $fullNote = '[' . $billStage . '] ' . $note;

        if (!empty($editId)) {
            $bill = SalesGenerate::findOrFail($editId);
            $bill->update([
                'bill_amount' => $billAmount,
                'amount' => $actualCost,
                'date' => $date,
                'note' => $fullNote,
                'discount' => $discount,
                'net_amount' => $netAmount,
            ]);

            ActivityLogger::log('Update', "Updated client bill ID {$editId} to {$billStage}");
            return redirect()->route('client-bills.index')->with('success', "Bill updated successfully to {$billStage}!");
        } else {
            $finalExists = SalesGenerate::where('project_name', $projectName)->where('note', 'like', '%[Final Bill]%')->exists();
            if ($finalExists) {
                return redirect()->route('client-bills.index')->with('error', 'A Final Bill already exists for this project! You cannot create new bills. Please edit the Final Bill instead.');
            }

            SalesGenerate::create([
                'company_name' => $companyName,
                'project_name' => $projectName,
                'amount' => $actualCost,
                'bill_amount' => $billAmount,
                'date' => $date,
                'note' => $fullNote,
                'payment_status' => 'Pending',
                'discount' => $discount,
                'net_amount' => $netAmount,
            ]);

            ActivityLogger::log('Create', "Created new {$billStage} for {$companyName} ({$projectName})");
            return redirect()->route('client-bills.index')->with('success', "New {$billStage} generated successfully!");
        }
    }

    public function show($id)
    {
        $bill = SalesGenerate::findOrFail($id);
        
        $stage = "Bill";
        $clean_note = $bill->note;
        if (preg_match('/\[(.*?)\]/', $bill->note, $match)) {
            $stage = $match[1];
            $clean_note = trim(str_replace('['.$stage.']', '', $bill->note));
        }

        $project = Project::where('project_name', $bill->project_name)
            ->where('company_name', $bill->company_name)
            ->first();
        $projectId = $project ? $project->id : 0;

        $breakdown_items = [];
        if ($projectId > 0) {
            // 1. Supplier Bills
            $sBills = SupplierBill::with('items.category')->where('project_id', $projectId)->get();
            foreach ($sBills as $sb) {
                $prods = $sb->items->map(function($i) { return $i->category->cat_name ?? ''; })->filter()->implode(', ');
                $breakdown_items[] = [
                    'type' => 'Material',
                    'desc' => "<b>Voucher: {$sb->voucher_no}</b>" . (!empty($prods) ? "<br><small>Items: {$prods}</small>" : "")
                ];
            }

            // 2. Legacy Supplier Bill Pay Status
            $sbPayStatus = DB::table('supplier_bill_pay_status')
                ->where(function($q) use ($bill, $projectId) {
                    $q->where('project_name', $bill->project_name)->orWhere('project_id', $projectId);
                })
                ->where('status', 'Bill')
                ->get();
            foreach ($sbPayStatus as $r) {
                if ((float)$r->amount > 0) {
                    $item = trim(($r->cat_name ?? '') . ' ' . ($r->item_name ?? ''));
                    $breakdown_items[] = [
                        'type' => 'Material',
                        'desc' => "<b>Party: " . ($r->party_name ?? '') . "</b>" . (!empty($item) ? "<br><small>Items: {$item}</small>" : "")
                    ];
                }
            }

            // 3. Project Expenses
            $pExpenses = ProjectExpense::where('project_id', $projectId)->get();
            foreach ($pExpenses as $pe) {
                $breakdown_items[] = [
                    'type' => 'Direct Exp.',
                    'desc' => "<b>Head: {$pe->expense_head}</b> (Vch: {$pe->voucher_no})"
                ];
            }

            // 4. General Transactions
            $gTrans = GeneralTransaction::where('project_id', $projectId)
                ->where('t_type', 'Payment')
                ->where('note', 'not like', '%Purchase%')
                ->where('note', 'not like', '%Cash Purchase%')
                ->get();
            foreach ($gTrans as $gt) {
                $rsn = $gt->note;
                if (str_contains($rsn, 'Reason:')) {
                    $parts = explode('Reason:', $rsn);
                    $rsn = trim($parts[1] ?? '');
                }
                $breakdown_items[] = [
                    'type' => 'Gen. Exp.',
                    'desc' => "<b>{$gt->payment_category}</b> (Vch: {$gt->check_no}) <br><small>Reason: {$rsn}</small>"
                ];
            }
        }

        $discount = (float)($bill->discount ?? 0);
        $billAmount = (float)$bill->bill_amount;
        $netAmount = ((float)$bill->net_amount > 0) ? (float)$bill->net_amount : max(0, $billAmount - $discount);

        return view('client_bills.show', compact('bill', 'stage', 'clean_note', 'breakdown_items', 'discount', 'billAmount', 'netAmount'));
    }

    public function destroy($id)
    {
        $bill = SalesGenerate::findOrFail($id);
        $bill->delete();

        ActivityLogger::log('Delete', "Deleted client bill ID {$id}");
        return redirect()->route('client-bills.index')->with('success', 'Bill deleted successfully.');
    }
}
