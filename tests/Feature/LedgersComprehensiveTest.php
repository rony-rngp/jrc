<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Party;
use App\Models\Project;
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
}
