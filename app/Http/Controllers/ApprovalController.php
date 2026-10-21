<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupplierBill;
use App\Models\SupplierPayment;
use App\Models\PaymentReceivedProject;
use App\Models\ProjectExpense;
use App\Models\GeneralTransaction;
use App\Models\PaymentMethod;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    /**
     * Pending Approvals Queue - Exactly matching legacy pending_approvals.php
     */
    public function pending()
    {
        // 1. Client Project Payments (Incomes)
        $clientPayments = PaymentReceivedProject::where('approval_status', 'Pending')
            ->where('val', 0)
            ->orderBy('date', 'desc')
            ->get();

        // 2. Supplier Bills (Only Bills & Dues)
        $supplierBills = SupplierBill::with(['supplier', 'project'])
            ->where('approval_status', 'Pending')
            ->orderBy('bill_date', 'desc')
            ->get();

        // 3. Supplier Payments (Expenses)
        $supplierPayments = SupplierPayment::with(['supplier', 'project'])
            ->where('approval_status', 'Pending')
            ->orderBy('t_date', 'desc')
            ->get();

        // 4. Project Direct Expenses
        $projectExpenses = ProjectExpense::with('project')
            ->where('approval_status', 'Pending')
            ->orderBy('t_date', 'desc')
            ->get();

        // 5. General / Loan Payments
        $generalPayments = GeneralTransaction::with('head')
            ->where('approval_status', 'Pending')
            ->where('t_type', 'Payment')
            ->orderBy('t_date', 'desc')
            ->get();

        return view('approvals.pending', compact(
            'clientPayments',
            'supplierBills',
            'supplierPayments',
            'projectExpenses',
            'generalPayments'
        ));
    }

    /**
     * Rejected Vouchers Archive - Exactly matching legacy rejected_vouchers.php
     */
    public function rejected()
    {
        $rejectedSupplierPayments = SupplierPayment::with(['supplier', 'project'])
            ->where('approval_status', 'Rejected')
            ->orderBy('t_date', 'desc')
            ->get();

        $rejectedSupplierBills = SupplierBill::with(['supplier', 'project'])
            ->where('approval_status', 'Rejected')
            ->orderBy('bill_date', 'desc')
            ->get();

        $rejectedProjectExpenses = ProjectExpense::with('project')
            ->where('approval_status', 'Rejected')
            ->orderBy('t_date', 'desc')
            ->get();

        $rejectedGeneralPayments = GeneralTransaction::with('head')
            ->where('approval_status', 'Rejected')
            ->where('t_type', 'Payment')
            ->orderBy('t_date', 'desc')
            ->get();

        $rejectedClientPayments = PaymentReceivedProject::where('approval_status', 'Rejected')
            ->orderBy('date', 'desc')
            ->get();

        return view('approvals.rejected', compact(
            'rejectedSupplierPayments',
            'rejectedSupplierBills',
            'rejectedProjectExpenses',
            'rejectedGeneralPayments',
            'rejectedClientPayments'
        ));
    }

    /**
     * Action handler matching legacy approve_action.php
     */
    public function handleAction(Request $request)
    {
        $action = $request->input('action');
        $id = (int)$request->input('id');
        $type = $request->input('type');

        return $this->processAction($action, $type, $id, $request);
    }

    public function approve(Request $request, $type, $id)
    {
        return $this->processAction('approve', $type, (int)$id, $request);
    }

    public function reject(Request $request, $type, $id)
    {
        return $this->processAction('reject', $type, (int)$id, $request);
    }

    public function restore(Request $request, $type, $id)
    {
        return $this->processAction('restore', $type, (int)$id, $request);
    }

    public function destroy(Request $request, $type, $id)
    {
        return $this->processAction('delete', $type, (int)$id, $request);
    }

    private function processAction($action, $type, $id, Request $request)
    {
        // Normalize type
        $normalizedType = match (strtolower($type)) {
            'income', 'client' => 'Income',
            'supplierbill', 'supplier_bill' => 'SupplierBill',
            'supplierpayment', 'supplier_payment' => 'Supplier',
            'project', 'projectexpense', 'project_expense' => 'Project',
            'general', 'generaltransaction', 'general_transaction' => 'General',
            default => $type
        };

        if (strtolower($type) === 'supplier') {
            if (SupplierPayment::where('id', $id)->exists()) {
                $normalizedType = 'Supplier';
            } elseif (SupplierBill::where('id', $id)->exists()) {
                $normalizedType = 'SupplierBill';
            } else {
                $normalizedType = 'Supplier';
            }
        }

        DB::beginTransaction();
        try {
            if ($action === 'approve') {
                if ($normalizedType === 'Income') {
                    $row = PaymentReceivedProject::lockForUpdate()->findOrFail($id);
                    if ($row->approval_status === 'Approved') {
                        throw new \Exception('This transaction is already approved!');
                    }
                    if ($row->method_id) {
                        PaymentMethod::where('id', $row->method_id)->increment('amount', (float)$row->pay_amount);
                    }
                    $row->approval_status = 'Approved';
                    $row->val = 0;
                    $row->save();
                } elseif ($normalizedType === 'SupplierBill') {
                    $row = SupplierBill::lockForUpdate()->findOrFail($id);
                    if ($row->approval_status === 'Approved') {
                        throw new \Exception('This transaction is already approved!');
                    }
                    $row->approval_status = 'Approved';
                    $row->save();
                } elseif ($normalizedType === 'Supplier') {
                    $row = SupplierPayment::lockForUpdate()->findOrFail($id);
                    if ($row->approval_status === 'Approved') {
                        throw new \Exception('This transaction is already approved!');
                    }
                    $amount = (float)$row->amount;
                    if ($row->method_id) {
                        PaymentMethod::where('id', $row->method_id)->decrement('amount', $amount);
                    }
                    $row->approval_status = 'Approved';
                    $row->save();

                    // Auto-adjust bill dues
                    if ($row->bill_id > 0) {
                        SupplierBill::where('id', $row->bill_id)->update([
                            'paid_amount' => DB::raw("paid_amount + $amount"),
                            'due_amount' => DB::raw("due_amount - $amount")
                        ]);
                    } else {
                        $remaining_pay = $amount;
                        $proj_cond = ($row->project_id > 0) ? [['project_id', '=', $row->project_id]] : [];
                        $dueBills = SupplierBill::where('supplier_id', $row->supplier_id)
                            ->where($proj_cond)
                            ->where('due_amount', '>', 0)
                            ->orderBy('bill_date', 'asc')
                            ->get();

                        foreach ($dueBills as $b) {
                            if ($remaining_pay <= 0) break;
                            $b_due = (float)$b->due_amount;
                            if ($remaining_pay >= $b_due) {
                                $pay_this = $b_due;
                                $remaining_pay -= $b_due;
                            } else {
                                $pay_this = $remaining_pay;
                                $remaining_pay = 0;
                            }
                            $b->paid_amount += $pay_this;
                            $b->due_amount -= $pay_this;
                            $b->save();
                        }
                    }
                } elseif ($normalizedType === 'Project') {
                    $row = ProjectExpense::lockForUpdate()->findOrFail($id);
                    if ($row->approval_status === 'Approved') {
                        throw new \Exception('This transaction is already approved!');
                    }
                    if ($row->method_id) {
                        PaymentMethod::where('id', $row->method_id)->decrement('amount', (float)$row->amount);
                    }
                    $row->approval_status = 'Approved';
                    $row->save();
                } elseif ($normalizedType === 'General') {
                    $row = GeneralTransaction::lockForUpdate()->findOrFail($id);
                    if ($row->approval_status === 'Approved') {
                        throw new \Exception('This transaction is already approved!');
                    }
                    $amt = (float)$row->amount;
                    if ($row->method_id) {
                        if ($row->t_type === 'Payment') {
                            PaymentMethod::where('id', $row->method_id)->decrement('amount', $amt);
                        } else {
                            PaymentMethod::where('id', $row->method_id)->increment('amount', $amt);
                        }
                    }
                    $row->approval_status = 'Approved';
                    $row->save();
                }

                ActivityLogger::log('Approve', "Approved {$normalizedType} ID #{$id}");
                DB::commit();
                return redirect()->back()->with('success', 'Transaction approved and balances updated successfully!');
            } elseif ($action === 'reject') {
                $rejectData = ['approval_status' => 'Rejected'];
                if ($request->filled('reject_reason')) {
                    $rejectData['reject_reason'] = $request->input('reject_reason');
                }

                if ($normalizedType === 'Income') {
                    $rejectData['val'] = 2;
                    PaymentReceivedProject::where('id', $id)->update($rejectData);
                } elseif ($normalizedType === 'SupplierBill') {
                    SupplierBill::where('id', $id)->update($rejectData);
                } elseif ($normalizedType === 'Supplier') {
                    SupplierPayment::where('id', $id)->update($rejectData);
                } elseif ($normalizedType === 'Project') {
                    ProjectExpense::where('id', $id)->update($rejectData);
                } elseif ($normalizedType === 'General') {
                    GeneralTransaction::where('id', $id)->update($rejectData);
                }

                ActivityLogger::log('Reject', "Rejected {$normalizedType} ID #{$id}");
                DB::commit();
                return redirect()->back()->with('success', 'Transaction moved to Rejected Archive.');
            } elseif ($action === 'restore') {
                if ($normalizedType === 'Income') {
                    PaymentReceivedProject::where('id', $id)->update(['approval_status' => 'Pending', 'val' => 0]);
                } elseif ($normalizedType === 'SupplierBill') {
                    SupplierBill::where('id', $id)->update(['approval_status' => 'Pending']);
                } elseif ($normalizedType === 'Supplier') {
                    SupplierPayment::where('id', $id)->update(['approval_status' => 'Pending']);
                } elseif ($normalizedType === 'Project') {
                    ProjectExpense::where('id', $id)->update(['approval_status' => 'Pending']);
                } elseif ($normalizedType === 'General') {
                    GeneralTransaction::where('id', $id)->update(['approval_status' => 'Pending']);
                }

                ActivityLogger::log('Restore', "Restored {$normalizedType} ID #{$id} to Pending");
                DB::commit();
                return redirect()->back()->with('success', 'Transaction restored to pending queue.');
            } elseif ($action === 'delete') {
                if ($normalizedType === 'Income') {
                    PaymentReceivedProject::where('id', $id)->delete();
                } elseif ($normalizedType === 'SupplierBill') {
                    SupplierBill::where('id', $id)->delete();
                } elseif ($normalizedType === 'Supplier') {
                    SupplierPayment::where('id', $id)->delete();
                } elseif ($normalizedType === 'Project') {
                    ProjectExpense::where('id', $id)->delete();
                } elseif ($normalizedType === 'General') {
                    GeneralTransaction::where('id', $id)->delete();
                }

                ActivityLogger::log('Delete', "Permanently deleted {$normalizedType} ID #{$id}");
                DB::commit();
                return redirect()->back()->with('success', 'Record permanently deleted.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }

        return redirect()->back()->with('error', 'Invalid action or transaction type.');
    }
}
