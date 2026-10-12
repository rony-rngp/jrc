<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\GeneralTransaction;
use App\Models\BalanceTransferHistory;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BalanceTransferController extends Controller
{
    public function index(Request $request)
    {
        // AJAX Request
        if ($request->has('ajax_request') || $request->ajax()) {
            if ($request->input('ajax_request') == 'get_accounts') {
                $type = $request->input('type');
                $accounts = PaymentMethod::where('account_type', $type)
                    ->orderBy('method_name', 'asc')
                    ->get()
                    ->map(function ($row) {
                        return [
                            'id' => $row->id,
                            'method_name' => $row->method_name,
                            'amount' => (float)$row->amount,
                            'balance' => number_format((float)$row->amount, 2),
                        ];
                    });
                return response()->json($accounts);
            }
        }

        $transfers = GeneralTransaction::where('trans_type', 'Transfer')
            ->where('t_type', 'Payment')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        return view('balance_transfers.index', compact('transfers'));
    }

    public function create()
    {
        return redirect()->route('balance-transfers.index');
    }

    public function store(Request $request)
    {
        if ($request->has('ajax_request') || $request->ajax()) {
            return $this->index($request);
        }

        $amount = (float)$request->input('amount');
        $voucher = $request->input('voucher') ?? ('TRF-' . strtoupper(uniqid()));
        $t_date = $request->input('t_date') ?? $request->input('date') ?? date('Y-m-d');
        $ref_no = $request->input('ref_no', '');
        $note = $request->input('note', '');

        $from_method_id = (int)$request->input('from_method_id');
        $to_method_id = (int)$request->input('to_method_id');

        $from_pm = $from_method_id ? PaymentMethod::find($from_method_id) : (
            $request->filled('source_method') ? PaymentMethod::where('method_name', $request->input('source_method'))->first() : null
        );

        $to_pm = $to_method_id ? PaymentMethod::find($to_method_id) : (
            $request->filled('des_name') ? PaymentMethod::where('method_name', $request->input('des_name'))->first() : (
                $request->filled('to_account_name') ? PaymentMethod::where('method_name', $request->input('to_account_name'))->first() : null
            )
        );

        if (!$from_pm || !$to_pm || $from_pm->id === $to_pm->id) {
            return redirect()->route('balance-transfers.index')->with('error', 'Please select two different valid accounts.');
        }

        $from_method_id = $from_pm->id;
        $to_method_id = $to_pm->id;

        if ($from_pm->amount < $amount) {
            return redirect()->route('balance-transfers.index')->with('error', "Insufficient Balance in {$from_pm->method_name}!");
        }

        $note_text = "Fund Transfer from {$from_pm->method_name} to {$to_pm->method_name}. " . $note;

        DB::beginTransaction();
        try {
            $from_pm->decrement('amount', $amount);
            $to_pm->increment('amount', $amount);

            // 1. Payment general_transaction
            GeneralTransaction::create([
                't_date' => $t_date,
                'head_id' => 0,
                'main_head' => 'Fund Transfer',
                'method_id' => $from_method_id,
                'amount' => $amount,
                'note' => $note_text,
                't_type' => 'Payment',
                'trans_type' => 'Transfer',
                'check_no' => $voucher,
                'ref_no' => $ref_no,
                'approval_status' => 'Approved',
            ]);

            // 2. Receive general_transaction
            GeneralTransaction::create([
                't_date' => $t_date,
                'head_id' => 0,
                'main_head' => 'Fund Transfer',
                'method_id' => $to_method_id,
                'amount' => $amount,
                'note' => $note_text,
                't_type' => 'Receive',
                'trans_type' => 'Transfer',
                'check_no' => $voucher,
                'ref_no' => $ref_no,
                'approval_status' => 'Approved',
            ]);

            // 3. Balance transfer history record for legacy compatibility
            BalanceTransferHistory::create([
                'source_method' => $from_pm->method_name,
                'des_name' => $to_pm->method_name,
                'amount' => $amount,
                'date' => $t_date,
                'note' => $note_text,
            ]);

            ActivityLogger::log('Transfer', "Transferred ৳{$amount} from {$from_pm->method_name} to {$to_pm->method_name} (Voucher: {$voucher})");
            DB::commit();

            return redirect()->route('balance-transfers.index')->with('success', "Fund transfer of ৳" . number_format($amount, 2) . " completed successfully! Voucher: {$voucher}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('balance-transfers.index')->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $voucher = $id; // Can be check_no voucher string or id
        $transfers = GeneralTransaction::where('check_no', $voucher)
            ->where('trans_type', 'Transfer')
            ->get();

        if ($transfers->isEmpty()) {
            $transfers = GeneralTransaction::where('id', $id)
                ->where('trans_type', 'Transfer')
                ->get();
            if ($transfers->isNotEmpty()) {
                $voucher = $transfers->first()->check_no;
                $transfers = GeneralTransaction::where('check_no', $voucher)->where('trans_type', 'Transfer')->get();
            }
        }

        $date = '';
        $amount = 0;
        $out_note = '';
        $in_note = '';

        foreach ($transfers as $row) {
            $date = date('d M Y', strtotime($row->t_date));
            $amount = (float)$row->amount;
            if ($row->t_type == 'Payment') {
                $out_note = $row->note;
            } else {
                $in_note = $row->note;
            }
        }

        $extractName = function ($text) {
            if (empty(trim($text))) return '';
            $first_part = explode("|", $text)[0];
            if (str_contains($first_part, ':')) {
                $arr = explode(":", $first_part);
                return trim($arr[1] ?? '');
            }
            return trim($first_part);
        };

        $from_account = $extractName($in_note);
        $to_account = $extractName($out_note);

        $trx_info = '-';
        $remarks_info = '';

        if (!empty($out_note)) {
            $parts = explode("|", $out_note);
            foreach ($parts as $part) {
                if (stripos($part, 'TrxID') !== false || stripos($part, 'Cheque') !== false) {
                    $trx_arr = explode(":", $part);
                    if (isset($trx_arr[1])) {
                        $trx_info = trim($trx_arr[1]);
                    }
                }
                if (stripos($part, 'Note') !== false) {
                    $note_arr = explode(":", $part);
                    if (isset($note_arr[1])) {
                        $remarks_info = trim($note_arr[1]);
                    }
                }
            }
        }

        if (request()->has('print') || request()->get('view') == 'print') {
            return view('balance_transfers.print', compact('voucher', 'date', 'amount', 'from_account', 'to_account', 'trx_info', 'remarks_info'));
        }

        return view('balance_transfers.show', compact('voucher', 'date', 'amount', 'from_account', 'to_account', 'trx_info', 'remarks_info'));
    }

    public function edit($id)
    {
        return $this->show($id);
    }

    public function destroy($id, Request $request)
    {
        $voucher = $request->input('voucher');

        DB::beginTransaction();
        try {
            $transfers = GeneralTransaction::where('check_no', $voucher)->where('trans_type', 'Transfer')->get();

            foreach ($transfers as $tx) {
                $pm = PaymentMethod::find($tx->method_id);
                if ($pm) {
                    if ($tx->t_type == 'Payment') {
                        $pm->increment('amount', $tx->amount);
                    } elseif ($tx->t_type == 'Receive') {
                        $pm->decrement('amount', $tx->amount);
                    }
                }
                $tx->delete();
            }

            ActivityLogger::log('Delete', "Reversed and deleted fund transfer Voucher: {$voucher}");
            DB::commit();

            return redirect()->route('balance-transfers.index')->with('success', "Transfer Voucher {$voucher} deleted and balances reverted successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('balance-transfers.index')->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}
