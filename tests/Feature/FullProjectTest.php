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
use App\Models\PaymentReceivedProject;
use App\Models\PaymentReceivedInfo;
use App\Models\HoPaymentStatus;
use App\Models\BalanceTransferHistory;
use App\Models\TblEmployeeSalary;
use App\Models\EmpSalaryAdvance;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class FullProjectTest extends TestCase
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

    /**
     * 1. Test Authentication
     */
    public function test_login_page_renders(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sign in to start your session');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => 'test_admin',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => 'test_admin',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * 2. Test All GET Routes and Blade Views
     */
    public function test_all_pages_render_successfully(): void
    {
        $this->actingAs($this->admin);

        $routes = [
            '/dashboard',
            '/clients',
            '/suppliers',
            '/projects',
            '/categories',
            '/account-heads',
            '/payment-methods',
            '/supplier-bills',
            '/supplier-bills/create',
            '/client-bills',
            '/client-bills/create',
            '/receive-vouchers',
            '/payment-vouchers',
            '/balance-transfers',
            '/employees',
            '/employees/create',
            '/salary/process',
            '/salary/advance',
            '/salary/history',
            '/ledger/client',
            '/ledger/supplier',
            '/ledger/project',
            '/ledger/loan',
            '/ledger/office-expense',
            '/ledger/account-statement',
            '/ledger/receipts-payments',
            '/top-ledger/balance-statement',
            '/top-ledger/client-summary',
            '/top-ledger/supplier-summary',
            '/top-ledger/employee-ledger',
            '/users',
            '/approvals/pending',
            '/approvals/rejected',
            '/activity-log',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
        }
    }

    /**
     * 3. Test Client CRUD
     */
    public function test_client_crud_lifecycle(): void
    {
        $this->actingAs($this->admin);

        $clientName = 'Test Client Corp ' . uniqid();

        // Create
        $response = $this->post('/clients', [
            'company_name' => $clientName,
            'persion_name' => 'John Doe',
            'contact' => '01700000000',
            'address' => 'Dhaka, Bangladesh',
            'amount' => 5000,
            'date' => date('Y-m-d'),
        ]);
        $response->assertRedirect('/clients');
        $this->assertDatabaseHas('client', ['company_name' => $clientName]);

        $client = Client::where('company_name', $clientName)->first();

        // Edit page
        $editResponse = $this->get("/clients/{$client->id}/edit");
        $editResponse->assertStatus(200);

        // Update
        $updatedName = $clientName . ' Updated';
        $updateResponse = $this->put("/clients/{$client->id}", [
            'company_name' => $updatedName,
            'persion_name' => 'John Doe Updated',
            'contact' => '01800000000',
            'address' => 'Chattogram',
            'date' => date('Y-m-d'),
        ]);
        $updateResponse->assertRedirect('/clients');
        $this->assertDatabaseHas('client', ['company_name' => $updatedName]);

        // Delete
        $deleteResponse = $this->delete("/clients/{$client->id}");
        $deleteResponse->assertRedirect('/clients');
        $this->assertDatabaseMissing('client', ['id' => $client->id]);
    }

    /**
     * 4. Test Supplier CRUD
     */
    public function test_supplier_crud_lifecycle(): void
    {
        $this->actingAs($this->admin);

        $supplierName = 'Test Supplier Ltd ' . uniqid();

        // Create
        $response = $this->post('/suppliers', [
            'party_name' => $supplierName,
            'party_phone' => '01900000000',
            'party_type' => 'Raw Materials',
            'address' => 'Dhaka',
            'amount' => 10000,
            'date' => date('Y-m-d'),
        ]);
        $response->assertRedirect('/suppliers');
        $this->assertDatabaseHas('party', ['party_name' => $supplierName]);

        $supplier = Party::where('party_name', $supplierName)->first();

        // Edit page
        $editResponse = $this->get("/suppliers/{$supplier->id}/edit");
        $editResponse->assertStatus(200);

        // Update
        $updatedName = $supplierName . ' Updated';
        $updateResponse = $this->put("/suppliers/{$supplier->id}", [
            'party_name' => $updatedName,
            'party_phone' => '01911111111',
            'party_type' => 'Cement & Steel',
            'address' => 'Gazipur',
            'amount' => 12000,
            'date' => date('Y-m-d'),
        ]);
        $updateResponse->assertRedirect('/suppliers');
        $this->assertDatabaseHas('party', ['party_name' => $updatedName]);

        // Delete
        $deleteResponse = $this->delete("/suppliers/{$supplier->id}");
        $deleteResponse->assertRedirect('/suppliers');
        $this->assertDatabaseMissing('party', ['id' => $supplier->id]);
    }

    /**
     * 5. Test Project CRUD & Ajax Duplicate Name Check
     */
    public function test_project_crud_and_ajax_check(): void
    {
        $this->actingAs($this->admin);

        $projectName = 'Tower Project ' . uniqid();
        $client = Client::first() ?? Client::create(['company_name' => 'Demo Client', 'created_on' => date('Y-m-d')]);

        // Ajax Check - Available
        $checkRes1 = $this->post('/projects/check-name', ['project_name' => $projectName]);
        $checkRes1->assertJson(['status' => 'available']);

        // Create
        $response = $this->post('/projects', [
            'company_name' => $client->company_name,
            'project_name' => $projectName,
            'estimated_value' => 500000,
            'address' => 'Mirpur, Dhaka',
            'date' => date('Y-m-d'),
        ]);
        $response->assertRedirect('/projects');
        $this->assertDatabaseHas('project', ['project_name' => $projectName]);

        // Ajax Check - Exists
        $checkRes2 = $this->post('/projects/check-name', ['project_name' => $projectName]);
        $checkRes2->assertJson(['status' => 'exists']);

        $proj = Project::where('project_name', $projectName)->first();

        // Edit page
        $editResponse = $this->get("/projects/{$proj->id}/edit");
        $editResponse->assertStatus(200);

        // Update
        $updateResponse = $this->put("/projects/{$proj->id}", [
            'company_name' => $client->company_name,
            'project_name' => $projectName,
            'estimated_value' => 600000,
            'address' => 'Uttara, Dhaka',
            'date' => date('Y-m-d'),
            'project_status' => 'Runing',
        ]);
        $updateResponse->assertRedirect('/projects');

        // Delete
        $deleteResponse = $this->delete("/projects/{$proj->id}");
        $deleteResponse->assertRedirect('/projects');
        $this->assertDatabaseMissing('project', ['id' => $proj->id]);
    }

    /**
     * 6. Test Product / Category CRUD
     */
    public function test_category_crud(): void
    {
        $this->actingAs($this->admin);

        $catName = 'Test Cat ' . uniqid();

        // Create
        $res = $this->post('/categories', [
            'cat_name' => $catName,
            'date' => date('Y-m-d'),
        ]);
        $res->assertRedirect('/categories');
        $this->assertDatabaseHas('material_category', ['cat_name' => $catName]);

        $cat = MaterialCategory::where('cat_name', $catName)->first();

        // Update
        $updName = $catName . ' Upd';
        $updRes = $this->put("/categories/{$cat->id}", ['cat_name' => $updName]);
        $updRes->assertRedirect('/categories');
        $this->assertDatabaseHas('material_category', ['cat_name' => $updName]);

        // Delete
        $delRes = $this->delete("/categories/{$cat->id}");
        $delRes->assertRedirect('/categories');
        $this->assertDatabaseMissing('material_category', ['id' => $cat->id]);
    }

    /**
     * 7. Test Account Head CRUD
     */
    public function test_account_head_crud(): void
    {
        $this->actingAs($this->admin);

        $headName = 'Test Head ' . uniqid();

        // Create
        $res = $this->post('/account-heads', [
            'head_name' => $headName,
            'head_type' => 'Expense',
        ]);
        $res->assertRedirect('/account-heads');
        $this->assertDatabaseHas('account_heads', ['head_name' => $headName]);

        $head = AccountHead::where('head_name', $headName)->first();

        // Update
        $updHead = $headName . ' Renamed';
        $updRes = $this->put("/account-heads/{$head->id}", [
            'head_name' => $updHead,
            'head_type' => 'Income',
        ]);
        $updRes->assertRedirect('/account-heads');
        $this->assertDatabaseHas('account_heads', ['head_name' => $updHead]);

        // Delete
        $delRes = $this->delete("/account-heads/{$head->id}");
        $delRes->assertRedirect('/account-heads');
        $this->assertDatabaseMissing('account_heads', ['id' => $head->id]);
    }

    /**
     * 8. Test Payment Method CRUD
     */
    public function test_payment_method_crud(): void
    {
        $this->actingAs($this->admin);

        $methodName = 'Test Bank ' . uniqid();

        // Create
        $res = $this->post('/payment-methods', [
            'method_name' => $methodName,
            'account_type' => 'Bank',
            'amount' => 50000,
        ]);
        $res->assertRedirect('/payment-methods');
        $this->assertDatabaseHas('payment_method', ['method_name' => $methodName]);

        $pm = PaymentMethod::where('method_name', $methodName)->first();

        // Update
        $updName = $methodName . ' Ltd';
        $updRes = $this->put("/payment-methods/{$pm->id}", [
            'method_name' => $updName,
            'account_type' => 'Bank',
            'amount' => 60000,
        ]);
        $updRes->assertRedirect('/payment-methods');
        $this->assertDatabaseHas('payment_method', ['method_name' => $updName]);

        // Delete
        $delRes = $this->delete("/payment-methods/{$pm->id}");
        $delRes->assertRedirect('/payment-methods');
        $this->assertDatabaseMissing('payment_method', ['id' => $pm->id]);
    }

    /**
     * 9. Test Purchase (Supplier Bill) Flow
     */
    public function test_supplier_bill_creation_and_invoice(): void
    {
        $this->actingAs($this->admin);

        $supplier = Party::first() ?? Party::create(['party_name' => 'Demo Supplier', 'date' => date('Y-m-d')]);
        $cat = MaterialCategory::first() ?? MaterialCategory::create(['cat_name' => 'Demo Mat', 'date' => date('Y-m-d')]);
        $pm = PaymentMethod::first() ?? PaymentMethod::create(['method_name' => 'CASH', 'amount' => 100000]);

        $voucherNo = 'TEST-SB-' . uniqid();

        // Create Bill with dynamic items
        $response = $this->post('/supplier-bills', [
            'voucher_no' => $voucherNo,
            'supplier_id' => $supplier->party_name,
            'project_id' => 0,
            'bill_date' => date('Y-m-d'),
            'paid_amount' => 500,
            'method_name' => $pm->method_name,
            'items' => [
                ['product_id' => $cat->id, 'qty' => 10, 'unit_price' => 100], // 1000
                ['product_id' => $cat->id, 'qty' => 5, 'unit_price' => 200],  // 1000
            ],
            'note' => 'Automated test purchase bill',
        ]);

        $response->assertRedirect('/supplier-bills');
        $this->assertDatabaseHas('supplier_bills', [
            'voucher_no' => $voucherNo,
            'grand_total' => 2000.00,
            'paid_amount' => 500.00,
            'due_amount' => 1500.00,
        ]);

        $bill = SupplierBill::where('voucher_no', $voucherNo)->first();

        // View/Invoice
        $showRes = $this->get("/supplier-bills/{$bill->id}");
        $showRes->assertStatus(200);
        $showRes->assertSee($voucherNo);

        // Delete
        $delRes = $this->delete("/supplier-bills/{$bill->id}");
        $delRes->assertRedirect('/supplier-bills');
        $this->assertDatabaseMissing('supplier_bills', ['id' => $bill->id]);
    }

    /**
     * 10. Test Sales (Client Bill) Flow
     */
    public function test_client_bill_creation_and_invoice(): void
    {
        $this->actingAs($this->admin);

        $client = Client::first() ?? Client::create(['company_name' => 'Demo Client 2', 'date' => date('Y-m-d')]);
        $pm = PaymentMethod::first() ?? PaymentMethod::create(['method_name' => 'CASH', 'amount' => 100000]);

        $note = 'Automated test client bill ' . uniqid();

        $response = $this->post('/client-bills', [
            'company_name' => $client->company_name,
            'project_name' => 'Demo Project',
            'bill_amount' => 5000,
            'discount' => 0,
            'bill_stage' => 'Running Bill',
            'date' => date('Y-m-d'),
            'note' => $note,
        ]);

        $response->assertRedirect('/client-bills');
        $this->assertDatabaseHas('tbl_sales_generate', [
            'company_name' => $client->company_name,
            'bill_amount' => 5000,
        ]);

        $bill = \App\Models\SalesGenerate::where('company_name', $client->company_name)->orderBy('id', 'desc')->first();

        // View
        $showRes = $this->get("/client-bills/{$bill->id}");
        $showRes->assertStatus(200);

        // Delete
        $delRes = $this->delete("/client-bills/{$bill->id}");
        $delRes->assertRedirect('/client-bills');
        $this->assertDatabaseMissing('tbl_sales_generate', ['id' => $bill->id]);
    }

    /**
     * 11. Test Receive Voucher & Payment Voucher
     */
    public function test_receive_and_payment_vouchers(): void
    {
        $this->actingAs($this->admin);

        $pm = PaymentMethod::first() ?? PaymentMethod::create(['method_name' => 'CASH', 'amount' => 100000]);
        $initialBalance = (float)$pm->amount;

        // Receive Voucher (+1000)
        $recRes = $this->post('/receive-vouchers', [
            'name' => 'General Collection',
            'method_name' => $pm->method_name,
            'amount' => 1000,
            'date' => date('Y-m-d'),
            'note' => 'Test Inflow',
        ]);
        $recRes->assertRedirect('/receive-vouchers');
        $this->assertEquals($initialBalance + 1000, (float)$pm->fresh()->amount);

        // Payment Voucher (-400)
        $payRes = $this->post('/payment-vouchers', [
            'expense_name' => 'Office Utility',
            'method_name' => $pm->method_name,
            'pay_amount' => 400,
            'pay_date' => date('Y-m-d'),
            'pay_note' => 'Test Outflow',
        ]);
        $payRes->assertRedirect('/payment-vouchers');
        $this->assertEquals($initialBalance + 1000 - 400, (float)$pm->fresh()->amount);
    }

    /**
     * 12. Test Fund Transfer Between Accounts
     */
    public function test_fund_transfer(): void
    {
        $this->actingAs($this->admin);

        $pm1 = PaymentMethod::create(['method_name' => 'Source Bank ' . uniqid(), 'amount' => 10000]);
        $pm2 = PaymentMethod::create(['method_name' => 'Dest Bank ' . uniqid(), 'amount' => 2000]);

        $response = $this->post('/balance-transfers', [
            'source_method' => $pm1->method_name,
            'des_name' => $pm2->method_name,
            'amount' => 3000,
            'date' => date('Y-m-d'),
            'note' => 'Fund transfer test',
        ]);

        $response->assertRedirect('/balance-transfers');
        $this->assertEquals(7000, (float)$pm1->fresh()->amount);
        $this->assertEquals(5000, (float)$pm2->fresh()->amount);

        // Clean up
        $pm1->delete();
        $pm2->delete();
    }

    /**
     * 13. Test Employee & Salary Payroll Flow
     */
    public function test_employee_and_salary_process(): void
    {
        $this->actingAs($this->admin);

        $pm = PaymentMethod::first() ?? PaymentMethod::create(['method_name' => 'CASH', 'amount' => 100000]);

        // Create Employee
        $emp = Employee::create([
            'employee_id' => 'EMP-' . rand(10000, 99999),
            'firstname' => 'Faruk',
            'lastname' => 'Ahmed',
            'address' => 'Dhaka',
            'birthdate' => '1995-01-01',
            'emp_nid' => '1234567890',
            'contact_info' => '01711111111',
            'gender' => 'Male',
            'position' => 'Staff',
            'schedule_id' => 1,
            'photo' => 'profile.jpg',
            'empType' => 'Permanent',
            'salary' => 25000,
            'mobile_bill' => 500,
            'lunch_bill' => 1500,
            'other_allowances' => 1000,
            'salary_advance' => 0,
            'pay_status' => 'Pending',
            'pay_month' => date('F Y'),
            'created_on' => date('Y-m-d'),
        ]);

        // Give Advance of 3000
        $advRes = $this->post('/salary/advance', [
            'employee_id' => $emp->id,
            'amount' => 3000,
            'method_id' => $pm->id,
            'advance_date' => date('Y-m-d'),
            'adjust_month' => date('F Y'),
        ]);
        $advRes->assertRedirect('/salary/advance');

        // Process Salary (Gross: 28000, Deduct Adv: 3000, Net: 25000)
        $salRes = $this->post('/salary/process', [
            'employee_id' => $emp->id,
            'amount' => 25000,
            'adv_deduct' => 3000,
            'method_name' => $pm->method_name,
            'pay_month' => date('F Y'),
            'date' => date('Y-m-d'),
        ]);
        $salRes->assertRedirect('/salary/process');

        $this->assertDatabaseHas('tbl_employee_salary', [
            'employee_id' => $emp->id,
            'amount' => '25000',
            'net_payable' => 22000.00,
        ]);

        // Clean up
        $emp->delete();
    }

    /**
     * 14. Test User Management
     */
    public function test_user_management(): void
    {
        $this->actingAs($this->admin);

        $uname = 'operator_' . uniqid();

        // Create
        $res = $this->post('/users', [
            'username' => $uname,
            'password' => 'secret123',
            'firstname' => 'New',
            'lastname' => 'Operator',
            'role' => 3,
        ]);
        $res->assertRedirect('/users');
        $this->assertDatabaseHas('admin', ['username' => $uname]);

        $u = Admin::where('username', $uname)->first();

        // Update
        $updRes = $this->put("/users/{$u->id}", [
            'firstname' => 'New First',
            'lastname' => 'Operator Last',
            'role' => 2,
        ]);
        $updRes->assertRedirect('/users');
        $this->assertDatabaseHas('admin', ['id' => $u->id, 'role' => 2]);

        // Delete
        $delRes = $this->delete("/users/{$u->id}");
        $delRes->assertRedirect('/users');
        $this->assertDatabaseMissing('admin', ['id' => $u->id]);
    }

    /**
     * 15. Test Voucher Approval Workflow
     */
    public function test_approval_workflow(): void
    {
        $this->actingAs($this->admin);

        $supplier = Party::first() ?? Party::create(['party_name' => 'Demo Supplier 3', 'date' => date('Y-m-d')]);

        $bill = SupplierBill::create([
            'bill_type' => 'General',
            'voucher_no' => 'APP-TEST-' . uniqid(),
            'supplier_id' => $supplier->party_name,
            'project_id' => 0,
            'bill_date' => date('Y-m-d'),
            'grand_total' => 1000,
            'paid_amount' => 0,
            'due_amount' => 1000,
            'approval_status' => 'Pending',
        ]);

        // Approve
        $appRes = $this->post("/approvals/supplier/{$bill->id}/approve");
        $this->assertEquals('Approved', $bill->fresh()->approval_status);

        // Reject with reason
        $rejRes = $this->post("/approvals/supplier/{$bill->id}/reject", [
            'reject_reason' => 'Duplicate entry suspected',
        ]);
        $this->assertEquals('Rejected', $bill->fresh()->approval_status);
        $this->assertEquals('Duplicate entry suspected', $bill->fresh()->reject_reason);

        // Clean up
        $bill->delete();
    }

    /**
     * 16. Test All AJAX Handlers & Dependable Dropdowns
     */
    public function test_all_ajax_handlers(): void
    {
        $this->actingAs($this->admin);

        // Receive Vouchers AJAX
        $res = $this->post('/receive-vouchers', ['ajax_request' => 'get_projects', 'client' => 'Demo Client']);
        $res->assertStatus(200);

        $res = $this->post('/receive-vouchers', ['ajax_request' => 'get_payment_methods', 'type' => 'Cash']);
        $res->assertStatus(200);

        $res = $this->post('/receive-vouchers', ['ajax_request' => 'check_voucher', 'voucher' => 'NON-EXISTENT-' . uniqid()]);
        $res->assertStatus(200)->assertJson(['status' => 'unique']);

        // Payment Vouchers AJAX
        $res = $this->post('/payment-vouchers', ['ajax_request' => 'get_sub_heads', 'parent_id' => 0]);
        $res->assertStatus(200);

        $res = $this->post('/payment-vouchers', ['ajax_request' => 'get_all_projects']);
        $res->assertStatus(200);

        // Supplier Bills AJAX
        $res = $this->post('/supplier-bills', ['ajax_request' => 'get_products']);
        $res->assertStatus(200);

        $res = $this->post('/supplier-bills', ['ajax_request' => 'get_suppliers']);
        $res->assertStatus(200);

        // Client Bills AJAX
        $res = $this->post('/client-bills', ['action' => 'get_projects_by_client', 'client_name' => 'Demo Client']);
        $res->assertStatus(200)->assertJsonStructure(['status', 'html']);

        // Balance Transfer AJAX
        $res = $this->post('/balance-transfers', ['ajax_request' => 'get_accounts', 'type' => 'Cash']);
        $res->assertStatus(200);

        // Salary AJAX
        $emp = Employee::first() ?? Employee::create(['firstname' => 'Test', 'lastname' => 'Emp', 'contact_info' => '01234', 'salary' => 15000]);
        $res = $this->post('/salary/process', ['ajax_request' => 'get_salary_info', 'emp_id' => $emp->id, 'month' => '2026-10']);
        $res->assertStatus(200);
    }
}

