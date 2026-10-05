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
}
