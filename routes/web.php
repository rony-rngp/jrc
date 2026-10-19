<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MaterialCategoryController;
use App\Http\Controllers\AccountHeadController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\SupplierBillController;
use App\Http\Controllers\ClientBillController;
use App\Http\Controllers\ReceiveVoucherController;
use App\Http\Controllers\PaymentVoucherController;
use App\Http\Controllers\BalanceTransferController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ActivityLogController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication routes (Login, Logout)
Auth::routes(['register' => false, 'reset' => false, 'verify' => false, 'confirm' => false]);

// Public / Realtime AJAX Check
Route::match(['get', 'post'], 'projects/check-name', [ProjectController::class, 'checkName'])->name('projects.check-name');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    // Profiles
    Route::resource('clients', ClientController::class);
    Route::resource('suppliers', SupplierController::class);
    
    // Projects
    Route::resource('projects', ProjectController::class);

    // Products / Material Categories
    Route::resource('categories', MaterialCategoryController::class);

    // Account Heads
    Route::resource('account-heads', AccountHeadController::class);

    // Payment Methods
    Route::resource('payment-methods', PaymentMethodController::class);

    // Purchase / Supplier Bills
    Route::resource('supplier-bills', SupplierBillController::class);

    // Sales / Client Bills
    Route::resource('client-bills', ClientBillController::class);

    // Accounts / Vouchers
    Route::resource('receive-vouchers', ReceiveVoucherController::class);
    Route::resource('payment-vouchers', PaymentVoucherController::class);
    Route::resource('balance-transfers', BalanceTransferController::class);

    // Employee Desk
    Route::resource('employees', EmployeeController::class);
    Route::get('salary/process', [SalaryController::class, 'process'])->name('salary.process');
    Route::post('salary/process', [SalaryController::class, 'storeProcess'])->name('salary.process.store');
    Route::get('salary/advance', [SalaryController::class, 'advance'])->name('salary.advance');
    Route::post('salary/advance', [SalaryController::class, 'storeAdvance'])->name('salary.advance.store');
    Route::post('salary/advance/delete', [SalaryController::class, 'destroyAdvance'])->name('salary.advance.delete');
    Route::get('salary/history', [SalaryController::class, 'history'])->name('salary.history');
    Route::get('salary/sheet/print', [SalaryController::class, 'printSheet'])->name('salary.sheet.print');
    Route::get('salary/payslip/{id}', [SalaryController::class, 'printSlip'])->name('salary.payslip');

    // Profile Update
    Route::post('profile/update', [UserController::class, 'updateProfile'])->name('users.profile.update');

    // All Ledgers
    Route::match(['get', 'post'], 'ledger/client', [LedgerController::class, 'clientLedger'])->name('ledgers.client');
    Route::match(['get', 'post'], 'ledger/client-print', [LedgerController::class, 'clientLedgerPrint'])->name('ledgers.client-print');
    Route::match(['get', 'post'], 'ledger/supplier', [LedgerController::class, 'supplierLedger'])->name('ledgers.supplier');
    Route::match(['get', 'post'], 'ledger/supplier-print', [LedgerController::class, 'supplierLedgerPrint'])->name('ledgers.supplier-print');
    Route::match(['get', 'post'], 'ledger/project', [LedgerController::class, 'projectLedger'])->name('ledgers.project');
    Route::match(['get', 'post'], 'ledger/project-print', [LedgerController::class, 'projectLedgerPrint'])->name('ledgers.project_print');
    Route::match(['get', 'post'], 'ledger/project-v2', [LedgerController::class, 'projectLedgerV2'])->name('ledgers.project_v2');
    Route::match(['get', 'post'], 'ledger/project-v2-print', [LedgerController::class, 'projectLedgerV2Print'])->name('ledgers.project_v2_print');
    Route::match(['get', 'post'], 'ledger/loan', [LedgerController::class, 'loanLedger'])->name('ledgers.loan');
    Route::match(['get', 'post'], 'ledger/office-expense', [LedgerController::class, 'officeExpenseLedger'])->name('ledgers.office_expense');
    Route::match(['get', 'post'], 'ledger/office-expense-alias', [LedgerController::class, 'officeExpenseLedger'])->name('ledgers.office-expense');
    Route::match(['get', 'post'], 'ledger/account-statement', [LedgerController::class, 'accountStatement'])->name('ledgers.account_statement');
    Route::match(['get', 'post'], 'ledger/account-statement-alias', [LedgerController::class, 'accountStatement'])->name('ledgers.account-statement');
    Route::match(['get', 'post'], 'ledger/receipts-payments', [LedgerController::class, 'receiptsPaymentsReport'])->name('ledgers.receipts_payments');
    Route::match(['get', 'post'], 'ledger/receipts-payments-alias', [LedgerController::class, 'receiptsPaymentsReport'])->name('ledgers.receipts-payments');

    // Top Ledgers
    Route::match(['get', 'post'], 'top-ledger/balance-statement', [LedgerController::class, 'balanceStatement'])->name('top-ledgers.balance-statement');
    Route::match(['get', 'post'], 'top-ledger/client-summary', [LedgerController::class, 'clientSummary'])->name('top-ledgers.client-summary');
    Route::match(['get', 'post'], 'top-ledger/supplier-summary', [LedgerController::class, 'supplierSummary'])->name('top-ledgers.supplier-summary');
    Route::match(['get', 'post'], 'top-ledger/employee-ledger', [LedgerController::class, 'employeeLedger'])->name('top-ledgers.employee-ledger');

    // User & Roles
    Route::resource('users', UserController::class);

    // Approvals
    Route::get('approvals/pending', [ApprovalController::class, 'pending'])->name('approvals.pending');
    Route::get('approvals/rejected', [ApprovalController::class, 'rejected'])->name('approvals.rejected');
    Route::post('approvals/{type}/{id}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('approvals/{type}/{id}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');

    // Activity Log
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
});
