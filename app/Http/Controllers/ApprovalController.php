<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupplierBill;
use App\Models\PaymentReceivedProject;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function pending()
    {
        $supplierBills = SupplierBill::where('approval_status', 'Pending')->get();
        $clientBills = PaymentReceivedProject::where('approval_status', 'Pending')->get();

        return view('approvals.pending', compact('supplierBills', 'clientBills'));
    }

    public function rejected()
    {
        $rejectedSupplierBills = SupplierBill::where('approval_status', 'Rejected')->get();
        $rejectedClientBills = PaymentReceivedProject::where('approval_status', 'Rejected')->get();

        return view('approvals.rejected', compact('rejectedSupplierBills', 'rejectedClientBills'));
    }

    public function approve(Request $request, $type, $id)
    {
        if ($type === 'supplier') {
            $bill = SupplierBill::findOrFail($id);
            $bill->update(['approval_status' => 'Approved']);
            ActivityLogger::log('Approve', "Approved Supplier Bill #{$bill->voucher_no}");
        } else {
            $bill = PaymentReceivedProject::findOrFail($id);
            $bill->update(['approval_status' => 'Approved']);
            ActivityLogger::log('Approve', "Approved Client Bill ID #{$bill->id}");
        }

        return redirect()->back()->with('success', 'Voucher approved successfully.');
    }

    public function reject(Request $request, $type, $id)
    {
        $reason = $request->input('reject_reason', 'Rejected by admin');

        if ($type === 'supplier') {
            $bill = SupplierBill::findOrFail($id);
            $bill->update(['approval_status' => 'Rejected', 'reject_reason' => $reason]);
            ActivityLogger::log('Reject', "Rejected Supplier Bill #{$bill->voucher_no} - {$reason}");
        } else {
            $bill = PaymentReceivedProject::findOrFail($id);
            $bill->update(['approval_status' => 'Rejected', 'reject_reason' => $reason]);
            ActivityLogger::log('Reject', "Rejected Client Bill ID #{$bill->id} - {$reason}");
        }

        return redirect()->back()->with('success', 'Voucher rejected.');
    }
}
