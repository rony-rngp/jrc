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
use App\Models\SalesGenerate;
use App\Models\PaymentReceivedProject;
use App\Models\HoPaymentStatus;
use App\Models\BalanceTransferHistory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

class RouteHealthTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::firstOrCreate(
            ['username' => 'test_admin_crawler'],
            [
                'password' => Hash::make('password123'),
                'firstname' => 'Crawler',
                'lastname' => 'Admin',
                'role' => 1,
                'created_on' => date('Y-m-d'),
                'empID' => 'TESTCRAWL01',
            ]
        );
    }

    /**
     * Test Every Single Registered GET Route
     */
    public function test_every_single_registered_get_route(): void
    {
        $this->actingAs($this->admin);

        $client = Client::first() ?? Client::create(['company_name' => 'Crawl Client', 'created_on' => date('Y-m-d')]);
        $supplier = Party::first() ?? Party::create(['party_name' => 'Crawl Supplier', 'date' => date('Y-m-d')]);
        $project = Project::first() ?? Project::create(['company_name' => $client->company_name, 'project_name' => 'Crawl Proj', 'date' => date('Y-m-d')]);
        $category = MaterialCategory::first() ?? MaterialCategory::create(['cat_name' => 'Crawl Cat', 'date' => date('Y-m-d')]);
        $head = AccountHead::first() ?? AccountHead::create(['head_name' => 'Crawl Head', 'head_type' => 'Expense']);
        $pm = PaymentMethod::first() ?? PaymentMethod::create(['method_name' => 'Crawl PM', 'amount' => 1000]);
        $emp = Employee::first() ?? Employee::create(['firstname' => 'Crawl', 'lastname' => 'Emp', 'contact_info' => '012', 'salary' => 10000]);
        $user = Admin::first();
        $sb = SupplierBill::first();
        $cb = SalesGenerate::first();
        $rv = PaymentReceivedProject::first();
        $pv = HoPaymentStatus::first();
        $bt = BalanceTransferHistory::first();

        $routes = Route::getRoutes()->getRoutes();
        $testedCount = 0;

        foreach ($routes as $route) {
            $methods = $route->methods();
            if (!in_array('GET', $methods)) {
                continue;
            }

            $uri = $route->uri();

            // Skip ignition, telescope, debugbar, storage local upload if any
            if (str_starts_with($uri, '_') || str_starts_with($uri, 'storage/') || str_starts_with($uri, 'up')) {
                continue;
            }

            // Replace route parameters with real IDs
            $resolvedUri = $uri;
            $resolvedUri = str_replace('{client}', (string)$client->id, $resolvedUri);
            $resolvedUri = str_replace('{supplier}', (string)$supplier->id, $resolvedUri);
            $resolvedUri = str_replace('{project}', (string)$project->id, $resolvedUri);
            $resolvedUri = str_replace('{category}', (string)$category->id, $resolvedUri);
            $resolvedUri = str_replace('{account_head}', (string)$head->id, $resolvedUri);
            $resolvedUri = str_replace('{payment_method}', (string)$pm->id, $resolvedUri);
            $resolvedUri = str_replace('{employee}', (string)$emp->id, $resolvedUri);
            $resolvedUri = str_replace('{user}', (string)$user->id, $resolvedUri);
            if ($sb) $resolvedUri = str_replace('{supplier_bill}', (string)$sb->id, $resolvedUri);
            if ($cb) $resolvedUri = str_replace('{client_bill}', (string)$cb->id, $resolvedUri);
            if ($rv) $resolvedUri = str_replace('{receive_voucher}', (string)$rv->id, $resolvedUri);
            if ($pv) $resolvedUri = str_replace('{payment_voucher}', (string)$pv->id, $resolvedUri);
            if ($bt) $resolvedUri = str_replace('{balance_transfer}', (string)$bt->id, $resolvedUri);

            // If still has unresolved braces, skip
            if (str_contains($resolvedUri, '{')) {
                continue;
            }

            $url = '/' . ltrim($resolvedUri, '/');
            $response = $this->get($url);

            // Assert no 500 server error
            $status = $response->getStatusCode();
            $this->assertTrue(
                in_array($status, [200, 301, 302, 303, 307, 308]),
                "Route GET {$url} returned unexpected HTTP status code: {$status}"
            );

            $testedCount++;
        }

        $this->assertGreaterThan(30, $testedCount);
    }
}
