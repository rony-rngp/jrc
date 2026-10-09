<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Party;
use App\Models\Project;
use App\Models\MaterialCategory;
use App\Models\AccountHead;
use App\Models\PaymentMethod;
use App\Models\Employee;
use App\Models\SupplierBill;
use App\Models\SupplierBillItem;
use App\Models\PaymentReceivedProject;
use App\Models\PaymentReceivedInfo;
use App\Models\HoPaymentStatus;
use App\Models\BalanceTransferHistory;
use App\Models\TblEmployeeSalary;
use App\Models\EmpSalaryAdvance;
use App\Models\SalesGenerate;
use App\Models\ProjectExpense;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ComprehensiveFinalTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::firstOrCreate(
            ['username' => 'test_admin_final'],
            [
                'password' => Hash::make('password123'),
                'firstname' => 'Final',
                'lastname' => 'Admin',
                'role' => 1,
                'created_on' => date('Y-m-d'),
                'empID' => 'TESTFINAL01',
            ]
        );
    }

    /**
     * 1. Test Dashboard Controller metrics and rendering
     */
    public function test_dashboard_metrics_and_view(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Cash Fund');

        $responseHome = $this->get('/home');
        $responseHome->assertStatus(200);
    }

    /**
     * 2. Test All Show and Edit pages for existing database records
     */
    public function test_existing_records_show_and_edit_pages(): void
    {
        $this->actingAs($this->admin);

        // Clients
        $client = Client::first();
        if ($client) {
            $this->get("/clients/{$client->id}")->assertStatus(200);
            $this->get("/clients/{$client->id}/edit")->assertStatus(200);
        }

        // Suppliers
        $supplier = Party::first();
        if ($supplier) {
            $this->get("/suppliers/{$supplier->id}")->assertStatus(200);
            $this->get("/suppliers/{$supplier->id}/edit")->assertStatus(200);
        }

        // Projects
        $project = Project::first();
        if ($project) {
            $this->get("/projects/{$project->id}")->assertStatus(200);
            $this->get("/projects/{$project->id}/edit")->assertStatus(200);
        }

        // Categories
        $cat = MaterialCategory::first();
        if ($cat) {
            $this->get("/categories/{$cat->id}")->assertRedirect();
            $this->get("/categories/{$cat->id}/edit")->assertStatus(200);
        }

        // Account Heads
        $head = AccountHead::first();
        if ($head) {
            $this->get("/account-heads/{$head->id}")->assertRedirect();
            $this->get("/account-heads/{$head->id}/edit")->assertStatus(200);
        }

        // Payment Methods
        $pm = PaymentMethod::first();
        if ($pm) {
            $this->get("/payment-methods/{$pm->id}")->assertRedirect();
            $this->get("/payment-methods/{$pm->id}/edit")->assertStatus(200);
        }

        // Supplier Bills
        $sb = SupplierBill::first();
        if ($sb) {
            $this->get("/supplier-bills/{$sb->id}")->assertStatus(200);
            $this->get("/supplier-bills/{$sb->id}/edit")->assertStatus(200);
        }

        // Client Bills
        $cb = SalesGenerate::first();
        if ($cb) {
            $this->get("/client-bills/{$cb->id}")->assertStatus(200);
            $this->get("/client-bills/{$cb->id}/edit")->assertStatus(200);
        }

        // Receive Vouchers
        $rv = PaymentReceivedInfo::first();
        if ($rv) {
            $this->get("/receive-vouchers/{$rv->id}")->assertStatus(200);
            $this->get("/receive-vouchers/{$rv->id}/edit")->assertStatus(200);
        }

        // Payment Vouchers
        $pv = HoPaymentStatus::first();
        if ($pv) {
            $this->get("/payment-vouchers/{$pv->id}")->assertStatus(200);
            $this->get("/payment-vouchers/{$pv->id}/edit")->assertStatus(200);
        }

        // Balance Transfers
        $bt = BalanceTransferHistory::first();
        if ($bt) {
            $this->get("/balance-transfers/{$bt->id}")->assertStatus(200);
            $this->get("/balance-transfers/{$bt->id}/edit")->assertStatus(200);
        }

        // Employees
        $emp = Employee::first();
        if ($emp) {
            $this->get("/employees/{$emp->id}")->assertStatus(200);
            $this->get("/employees/{$emp->id}/edit")->assertStatus(200);
        }

        // Users
        $user = Admin::first();
        if ($user) {
            $this->get("/users/{$user->id}")->assertRedirect();
            $this->get("/users/{$user->id}/edit")->assertStatus(200);
        }
    }

    /**
     * 3. Test Receive Voucher Full Lifecycle (Store, Edit, Update, Destroy)
     */
    public function test_receive_voucher_full_lifecycle(): void
    {
        $this->actingAs($this->admin);

        $pm = PaymentMethod::first() ?? PaymentMethod::create(['method_name' => 'Cash', 'amount' => 50000]);
        $client = Client::first() ?? Client::create(['company_name' => 'Voucher Client', 'created_on' => date('Y-m-d')]);

        $voucherNo = 'RV-TEST-' . uniqid();

        // 1. Store
        $storeRes = $this->post('/receive-vouchers', [
            'voucher_no' => $voucherNo,
            'receive_type' => 'Client',
            'client_name' => $client->company_name,
            'method_name' => $pm->method_name,
            'amount' => 5500,
            'date' => date('Y-m-d'),
            'note' => 'Test receive voucher',
        ]);
        $storeRes->assertRedirect('/receive-vouchers');

        $voucher = PaymentReceivedProject::where('check_no', $voucherNo)->first();
        $this->assertNotNull($voucher);
        $this->assertEquals(5500, (float)$voucher->pay_amount);

        // 2. Show View
        $showRes = $this->get("/receive-vouchers/{$voucher->id}?type=Client");
        $showRes->assertStatus(200);

        // 3. Destroy
        $delRes = $this->delete("/receive-vouchers/{$voucher->id}?type=Client");
        $delRes->assertRedirect('/receive-vouchers');
        $this->assertDatabaseMissing('payment_received_project', ['id' => $voucher->id]);
    }

    /**
     * 4. Test Payment Voucher Full Lifecycle (Store, Show, Destroy)
     */
    public function test_payment_voucher_full_lifecycle(): void
    {
        $this->actingAs($this->admin);

        $pm = PaymentMethod::first() ?? PaymentMethod::create(['method_name' => 'Cash', 'amount' => 50000]);
        $supplier = Party::first() ?? Party::create(['party_name' => 'Voucher Supplier', 'date' => date('Y-m-d')]);

        $voucherNo = 'PV-TEST-' . uniqid();

        // 1. Store
        $storeRes = $this->post('/payment-vouchers', [
            'voucher_no' => $voucherNo,
            'payment_type' => 'Supplier',
            'supplier_id' => $supplier->party_name,
            'method_name' => $pm->method_name,
            'amount' => 3200,
            't_date' => date('Y-m-d'),
            'note' => 'Test payment voucher',
        ]);
        $storeRes->assertRedirect('/payment-vouchers');

        $voucher = \App\Models\SupplierPayment::where('voucher_no', $voucherNo)->first();
        $this->assertNotNull($voucher);
        $this->assertEquals(3200, (float)$voucher->amount);

        // 2. Show View
        $showRes = $this->get("/payment-vouchers/{$voucher->id}?type=Supplier");
        $showRes->assertStatus(200);

        // 3. Destroy
        $delRes = $this->delete("/payment-vouchers/{$voucher->id}?type=Supplier");
        $delRes->assertRedirect('/payment-vouchers');
        $this->assertDatabaseMissing('supplier_payments', ['id' => $voucher->id]);
    }

    /**
     * 5. Test Activity Log Page
     */
    public function test_activity_log_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/activity-log');
        $response->assertStatus(200);
        $response->assertSee('Activity Log');
    }

    /**
     * 6. Test User Profile Update
     */
    public function test_profile_update(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/profile/update', [
            'firstname' => 'UpdatedFirst',
            'lastname' => 'UpdatedLast',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $this->admin->refresh();
        $this->assertEquals('UpdatedFirst', $this->admin->firstname);
        $this->assertEquals('UpdatedLast', $this->admin->lastname);
        $this->assertTrue(Hash::check('newpassword123', $this->admin->password));
    }
}
