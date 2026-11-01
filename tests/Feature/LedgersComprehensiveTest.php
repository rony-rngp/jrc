<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Party;
use App\Models\Project;
use App\Models\Employee;
use App\Models\PaymentMethod;
use App\Models\AccountHead;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LedgersComprehensiveTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::firstOrCreate(
            ['username' => 'test_admin'],
            [
                'password' => Hash::make('password123'),
                'firstname' => 'Test',
                'lastname' => 'Admin',
                'role' => 1,
                'created_on' => date('Y-m-d'),
                'empID' => 'TEST01',
            ]
        );
    }

    public function test_all_ledger_pages_render_and_filter(): void
    {
        $this->actingAs($this->admin);

        // 1. Client Ledger (Index & Filter)
        $client = Client::first();
        $res = $this->get('/ledger/client');
        $res->assertStatus(200);
        $res->assertSee('active menu-open');
        $res->assertSee('Client Ledger');

        if ($client) {
            $res = $this->get('/ledger/client?client_name=' . urlencode($client->company_name) . '&from_date=2025-01-01&to_date=' . date('Y-m-d') . '&view_ledger=1');
            $res->assertStatus(200);
            $res->assertSee($client->company_name);

            $res = $this->get('/ledger/client-print?client=' . urlencode($client->company_name) . '&from=2025-01-01&to=' . date('Y-m-d'));
            $res->assertStatus(200);
            $res->assertSee($client->company_name);
        }

        // 2. Supplier Ledger (Index & Filter)
        $supplier = Party::first();
        $res = $this->get('/ledger/supplier');
        $res->assertStatus(200);

        if ($supplier) {
            $res = $this->get('/ledger/supplier?supplier_id=' . $supplier->id . '&from_date=2025-01-01&to_date=' . date('Y-m-d') . '&view_ledger=1');
            $res->assertStatus(200);
            $res->assertSee($supplier->party_name);

            $res = $this->get('/ledger/supplier-print?supplier=' . $supplier->id . '&from=2025-01-01&to=' . date('Y-m-d'));
            $res->assertStatus(200);
            $res->assertSee($supplier->party_name);
        }

        // 3. Project Ledger (Index & Filter)
        $project = Project::first();
        $res = $this->get('/ledger/project');
        $res->assertStatus(200);

        if ($project) {
            $res = $this->get('/ledger/project?project_id=' . $project->id . '&from_date=2025-01-01&to_date=' . date('Y-m-d') . '&view_ledger=1');
            $res->assertStatus(200);
            $res->assertSee($project->project_name);
        }

        // 4. Loan Ledger (Index & Filter)
        $loanHead = AccountHead::where('head_type', 'Receive')->first();
        $res = $this->get('/ledger/loan');
        $res->assertStatus(200);

        if ($loanHead) {
            $res = $this->get('/ledger/loan?loan_head_id=' . $loanHead->id . '&from_date=2025-01-01&to_date=' . date('Y-m-d') . '&view_ledger=1');
            $res->assertStatus(200);
            $res->assertSee($loanHead->head_name);
        }

        // 5. Office Expense Ledger (Index & Filter)
        $res = $this->get('/ledger/office-expense');
        $res->assertStatus(200);

        $res = $this->get('/ledger/office-expense?head_id=all&from_date=2025-01-01&to_date=' . date('Y-m-d') . '&view_ledger=1');
        $res->assertStatus(200);
        $res->assertSee('Office Expenses');

        // 6. Account Statement (Index & Filter)
        $pm = PaymentMethod::first();
        $res = $this->get('/ledger/account-statement');
        $res->assertStatus(200);

        if ($pm) {
            $res = $this->get('/ledger/account-statement?method_id=' . $pm->id . '&from_date=2025-01-01&to_date=' . date('Y-m-d') . '&view_statement=1');
            $res->assertStatus(200);
            $res->assertSee(strtoupper($pm->method_name));
        }

        // 7. Receipts & Payments Report (Index & Filter)
        $res = $this->get('/ledger/receipts-payments');
        $res->assertStatus(200);

        $res = $this->get('/ledger/receipts-payments?from_date=2025-01-01&to_date=' . date('Y-m-d') . '&view_report=1');
        $res->assertStatus(200);
        $res->assertSee('Receipts and Payments Account');
    }

    public function test_all_top_ledger_pages_render_and_filter(): void
    {
        $this->actingAs($this->admin);

        // 1. Balance Statement
        $res = $this->get('/top-ledger/balance-statement');
        $res->assertStatus(200);
        $res->assertSee('Statement of Financial Position');
        $res->assertSee('ASSETS');
        $res->assertSee('LIABILITIES');

        // 2. Client Summary
        $res = $this->get('/top-ledger/client-summary');
        $res->assertStatus(200);
        $res->assertSee('Outstanding Balances Summary');
        $res->assertSee('Total Period Billed');

        // 3. Supplier Summary
        $res = $this->get('/top-ledger/supplier-summary');
        $res->assertStatus(200);
        $res->assertSee('Consolidated Supplier Balances');
        $res->assertSee('Period Billed');

        // 4. Employee Ledger
        $res = $this->get('/top-ledger/employee-ledger');
        $res->assertStatus(200);
        $res->assertSee('Employee Balances Summary');
        $res->assertSee('Period Earned');
    }

    public function test_project_ledger_v2_view_modes(): void
    {
        $this->actingAs($this->admin);

        $res = $this->get('/ledger/project-v2');
        $res->assertStatus(200);
        $res->assertSee('Project Ledger V2');
        $res->assertSee('Perticular wise');

        $project = Project::find(26) ?: Project::first();
        if ($project) {
            // 1. Detailed mode
            $res = $this->get('/ledger/project-v2?project_id=' . $project->id . '&view_mode=detailed');
            $res->assertStatus(200);
            $res->assertSee('TOATAL PROJECT COST');

            // 2. Summary mode
            $res = $this->get('/ledger/project-v2?project_id=' . $project->id . '&view_mode=summary');
            $res->assertStatus(200);
            $res->assertSee('TOATAL PROJECT COST');

            // 3. Particular wise mode
            $res = $this->get('/ledger/project-v2?project_id=' . $project->id . '&view_mode=particular');
            $res->assertStatus(200);
            $res->assertSee('TOATAL PROJECT COST');

            // 4. Particular wise print view
            $res = $this->get('/ledger/project-v2-print?project_id=' . $project->id . '&view_mode=particular');
            $res->assertStatus(200);
            $res->assertSee('TOATAL PROJECT COST');
        }
    }

    public function test_supplier_ledger_shows_all_vouchers_including_voucher_524(): void
    {
        $this->actingAs($this->admin);

        // Supplier 1 (Ma Thai & Interior) with date range 2026-09-01 to 2026-11-01
        $res = $this->get('/ledger/supplier?supplier_id=1&from_date=2026-09-01&to_date=2026-11-01&view_ledger=1');
        $res->assertStatus(200);
        $res->assertSee('1,478,521.00'); // Brought Forward
        $res->assertSee('523');
        $res->assertSee('17,721.00');
        $res->assertSee('524');
        $res->assertSee('17,373.00');
        $res->assertSee('525');
        $res->assertSee('6,895.00');
        $res->assertSee('3870');
        $res->assertSee('35,000.00');
        $res->assertSee('3940');
        $res->assertSee('300,000.00');
        $res->assertSee('3953');
        $res->assertSee('50,000.00');
        $res->assertSee('3954');
        $res->assertSee('26,400.00');
        $res->assertSee('411,400.00'); // Total Dr
        $res->assertSee('41,989.00');  // Total Cr
        $res->assertSee('1,847,932.00'); // Closing Balance

        // Make sure unrelated payments with supplier_id=0 are NOT included
        $res->assertDontSee('3895');
        $res->assertDontSee('3893');

        // Test Print View
        $printRes = $this->get('/ledger/supplier-print?supplier_id=1&from=2026-09-01&to=2026-11-01');
        $printRes->assertStatus(200);
        $printRes->assertSee('1,478,521.00');
        $printRes->assertSee('524');
        $printRes->assertSee('17,373.00');
        $printRes->assertSee('411,400.00');
        $printRes->assertSee('41,989.00');
        $printRes->assertSee('1,847,932.00');
    }

    public function test_employee_salary_sheet_print_layout_and_calculation(): void
    {
        $this->actingAs($this->admin);

        // Test employee 3 print sheet
        $res = $this->get('/salary/sheet/print?employee_id=3&from_date=&to_date=&pay_month=');
        $res->assertStatus(200);
        $res->assertSee('EMPLOYEE SALARY & PAYMENT LEDGER', false);
        $res->assertSee('Shakilur Rahman');
        $res->assertSee('Basic Salary');
        $res->assertSee('Allowances');
        $res->assertSee('Gross Salary');
        $res->assertSee('Deductions');
        $res->assertSee('Net Payable');
        $res->assertSee('Paid Amount');
        $res->assertSee('Due / (Advance)');
        $res->assertSee('Voucher No');
        $res->assertSee('TOTAL');
        $res->assertSee('Received By');
        $res->assertSee('Accounts Manager');
        $res->assertSee('Checked By');
        $res->assertSee('Audit By');
        $res->assertSee('Approved By');
    }

    public function test_multiple_salary_entries_and_installments_in_same_month(): void
    {
        $this->actingAs($this->admin);

        // 1. Create a test employee with basic salary 50,000
        $emp = Employee::create([
            'employee_id' => 'EMP-MULTI-' . rand(1000, 9999),
            'firstname' => 'TestMulti',
            'lastname' => 'Employee',
            'contact_info' => '01700000000',
            'salary' => 50000,
            'mobile_bill' => 0,
            'lunch_bill' => 0,
            'other_allowances' => 0,
        ]);

        $targetMonth = '2026-11';

        // 2. Initial AJAX check: no prior entries in November
        $res = $this->post('/salary/process', [
            'ajax_request' => 'get_salary_info',
            'emp_id' => $emp->id,
            'month' => $targetMonth
        ]);
        $res->assertStatus(200);
        $res->assertJson([
            'status' => 'unpaid',
            'has_prior_entry' => false,
            'salary' => 50000,
            'previous_due' => 0,
        ]);

        // 3. Installment 1: Pay 10,000 out of 50,000
        $vch1 = 'SAL-NOV-01-' . rand(100, 999);
        $res1 = $this->post('/salary/process', [
            'employee_id' => $emp->id,
            'pay_month' => $targetMonth,
            'date' => '2026-11-05',
            'voucher' => $vch1,
            'main_salary' => 50000,
            'paid_amount' => 10000,
            'previous_due' => 0,
            'method_name' => 'Cash',
            'deposit_category' => 'Cash',
        ]);
        $res1->assertRedirect('/salary/process');
        $res1->assertSessionHas('success');

        $this->assertDatabaseHas('tbl_employee_salary', [
            'employee_id' => $emp->id,
            'pay_month' => $targetMonth,
            'voucher_no' => $vch1,
            'main_salary' => '50000',
            'amount' => '10000',
            'due_amount' => 40000.00,
            'due_status' => 'Pending',
        ]);

        // 4. AJAX check again: should NOT be blocked as paid, should indicate 1 prior entry with remaining due
        $res = $this->post('/salary/process', [
            'ajax_request' => 'get_salary_info',
            'emp_id' => $emp->id,
            'month' => $targetMonth
        ]);
        $res->assertStatus(200);
        $res->assertJson([
            'status' => 'unpaid',
            'has_prior_entry' => true,
            'prior_count' => 1,
            'prior_paid' => 10000,
            'previous_due' => 40000,
            'salary' => 0, // already in previous_due
        ]);

        // 5. Installment 2: Pay 5,000 against the 40,000 previous due
        $vch2 = 'SAL-NOV-02-' . rand(100, 999);
        $res2 = $this->post('/salary/process', [
            'employee_id' => $emp->id,
            'pay_month' => $targetMonth,
            'date' => '2026-11-15',
            'voucher' => $vch2,
            'main_salary' => 0,
            'previous_due' => 40000,
            'paid_amount' => 5000,
            'method_name' => 'Cash',
            'deposit_category' => 'Cash',
        ]);
        $res2->assertRedirect('/salary/process');
        $res2->assertSessionHas('success');

        $this->assertDatabaseHas('tbl_employee_salary', [
            'employee_id' => $emp->id,
            'pay_month' => $targetMonth,
            'voucher_no' => $vch2,
            'amount' => '5000',
            'due_amount' => 35000.00,
            'due_status' => 'Pending',
        ]);

        // 6. Installment 3: Pay 20,000 against the 35,000 previous due
        $vch3 = 'SAL-NOV-03-' . rand(100, 999);
        $res3 = $this->post('/salary/process', [
            'employee_id' => $emp->id,
            'pay_month' => $targetMonth,
            'date' => '2026-11-25',
            'voucher' => $vch3,
            'main_salary' => 0,
            'previous_due' => 35000,
            'paid_amount' => 20000,
            'method_name' => 'Cash',
            'deposit_category' => 'Cash',
        ]);
        $res3->assertRedirect('/salary/process');
        $res3->assertSessionHas('success');

        $this->assertDatabaseHas('tbl_employee_salary', [
            'employee_id' => $emp->id,
            'pay_month' => $targetMonth,
            'voucher_no' => $vch3,
            'amount' => '20000',
            'due_amount' => 15000.00,
            'due_status' => 'Pending',
        ]);

        // 7. Installment 4: Pay 25,000 (15,000 clears due + 10,000 extra becomes advance for December!)
        $vch4 = 'SAL-NOV-04-' . rand(100, 999);
        $res4 = $this->post('/salary/process', [
            'employee_id' => $emp->id,
            'pay_month' => $targetMonth,
            'date' => '2026-11-28',
            'voucher' => $vch4,
            'main_salary' => 0,
            'previous_due' => 15000,
            'paid_amount' => 25000,
            'method_name' => 'Cash',
            'deposit_category' => 'Cash',
        ]);
        $res4->assertRedirect('/salary/process');
        $res4->assertSessionHas('success');

        // Check advance created for December
        $this->assertDatabaseHas('emp_salary_advance', [
            'employee_id' => $emp->id,
            'amount' => '10000.00',
            'adjust_month' => '2026-12',
            'status' => 'Pending',
        ]);

        // 8. Verify print sheet displays all installments
        $sheetRes = $this->get('/salary/sheet/print?employee_id=' . $emp->id . '&pay_month=' . $targetMonth);
        $sheetRes->assertStatus(200);
        $sheetRes->assertSee($vch1);
        $sheetRes->assertSee($vch2);
        $sheetRes->assertSee($vch3);
        $sheetRes->assertSee($vch4);

        // Clean up test employee
        DB::table('tbl_employee_salary')->where('employee_id', $emp->id)->delete();
        DB::table('emp_salary_advance')->where('employee_id', $emp->id)->delete();
        $emp->delete();
    }
}
