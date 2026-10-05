<aside class="main-sidebar">
    <section class="sidebar">
      <div class="user-panel">
        <div class="pull-left image">
          <img src="{{ !empty(Auth::user()->photo) ? asset('images/' . Auth::user()->photo) : asset('images/profile.jpg') }}" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>{{ Auth::user()->firstname ?? 'Admin' }} {{ Auth::user()->lastname ?? '' }}</p>
          <a><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>

      <ul class="sidebar-menu" data-widget="tree">
        
       <li class="{{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'active' : '' }}">
         <a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
       </li>
       
       @php
         $isAddProfileActive = request()->is('clients*') || request()->is('suppliers*') || request()->is('projects*') || request()->is('categories*') || request()->is('account-heads*') || request()->is('payment-methods*');
       @endphp
       <li class="treeview {{ $isAddProfileActive ? 'active menu-open' : '' }}">
          <a href="#">
            <i class="fa fa-plus" aria-hidden="true"></i>
            <span>Add Profile</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" {!! $isAddProfileActive ? 'style="display: block;"' : '' !!}>
            <li class="{{ request()->routeIs('clients.*') ? 'active' : '' }}"><a href="{{ route('clients.index') }}"><i class="fa fa-circle-o"></i>Add Client</a></li>
            <li class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><a href="{{ route('suppliers.index') }}"><i class="fa fa-circle-o"></i>Add Supplier</a></li>
            <li class="{{ request()->routeIs('projects.*') ? 'active' : '' }}"><a href="{{ route('projects.index') }}"><i class="fa fa-circle-o"></i>Add Projects</a></li>
            <li class="{{ request()->routeIs('categories.*') ? 'active' : '' }}"><a href="{{ route('categories.index') }}"><i class="fa fa-circle-o"></i>Add Products</a></li>
            <li class="{{ request()->routeIs('account-heads.*') ? 'active' : '' }}"><a href="{{ route('account-heads.index') }}"><i class="fa fa-circle-o"></i>Add Account Head</a></li>
            <li class="{{ request()->routeIs('payment-methods.*') ? 'active' : '' }}"><a href="{{ route('payment-methods.index') }}"><i class="fa fa-circle-o"></i>Add Payment Method</a></li>
          </ul>
        </li>
        
        @php
          $isPurchaseActive = request()->is('supplier-bills*');
        @endphp
        <li class="treeview {{ $isPurchaseActive ? 'active menu-open' : '' }}">
          <a href="#">
            <i class="fa fa-shopping-cart"></i>
            <span>Purchase </span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" {!! $isPurchaseActive ? 'style="display: block;"' : '' !!}>
            <li class="{{ request()->routeIs('supplier-bills.*') ? 'active' : '' }}"><a href="{{ route('supplier-bills.index') }}"><i class="fa fa-circle-o"></i>Purchase (Supplier Bill)</a></li>
          </ul>
        </li>

        @php
          $isSalesActive = request()->is('client-bills*');
        @endphp
        <li class="treeview {{ $isSalesActive ? 'active menu-open' : '' }}">
          <a href="#">
            <i class="fa fa-tags" aria-hidden="true"></i>
            <span> Sales </span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" {!! $isSalesActive ? 'style="display: block;"' : '' !!}>
            <li class="{{ request()->routeIs('client-bills.*') ? 'active' : '' }}"><a href="{{ route('client-bills.index') }}"><i class="fa fa-circle-o"></i> Project Sales (Client Bill) </a></li>
          </ul>
        </li>
        
        @php
          $isAccountsActive = request()->is('receive-vouchers*') || request()->is('payment-vouchers*') || request()->is('balance-transfers*');
        @endphp
        <li class="treeview {{ $isAccountsActive ? 'active menu-open' : '' }}">
          <a href="#">
            <i class="fa fa-bank"></i>
            <span>Accounts</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" {!! $isAccountsActive ? 'style="display: block;"' : '' !!}>
              <li class="{{ request()->routeIs('receive-vouchers.*') ? 'active' : '' }}"><a href="{{ route('receive-vouchers.index') }}"><i class="fa fa-star-o"></i>Received</a></li>
              <li class="{{ request()->routeIs('payment-vouchers.*') ? 'active' : '' }}"><a href="{{ route('payment-vouchers.index') }}"><i class="fa fa-star-o"></i>Payment</a></li>
              <li class="{{ request()->routeIs('balance-transfers.*') ? 'active' : '' }}"><a href="{{ route('balance-transfers.index') }}"><i class="fa fa-star-o"></i>Fund Transfar</a></li>    
          </ul>
        </li>

        @php
          $isEmployeeActive = request()->is('employees*') || request()->is('salary*');
        @endphp
        <li class="treeview {{ $isEmployeeActive ? 'active menu-open' : '' }}">
          <a href="#">
            <i class="fa fa-users" aria-hidden="true"></i>
            <span>Employee Desk</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" {!! $isEmployeeActive ? 'style="display: block;"' : '' !!}>
            <li class="{{ request()->routeIs('salary.process') ? 'active' : '' }}"><a href="{{ route('salary.process') }}"><i class="fa fa-circle-o"></i>Salary Pay</a></li>
            <li class="{{ request()->routeIs('salary.advance') ? 'active' : '' }}"><a href="{{ route('salary.advance') }}"><i class="fa fa-circle-o"></i>Salary Advance</a></li>
            <li class="{{ request()->routeIs('salary.history') ? 'active' : '' }}"><a href="{{ route('salary.history') }}"><i class="fa fa-circle-o"></i>Salary History</a></li>
            <li class="{{ request()->routeIs('employees.create') ? 'active' : '' }}"><a href="{{ route('employees.create') }}"><i class="fa fa-circle-o"></i>Employee Add</a></li>
            <li class="{{ request()->routeIs('employees.index') ? 'active' : '' }}"><a href="{{ route('employees.index') }}"><i class="fa fa-circle-o"></i>Employee List</a></li>
          </ul>
        </li>

        @php
          $isLedgersActive = request()->is('ledgers*');
        @endphp
        <li class="treeview {{ $isLedgersActive ? 'active menu-open' : '' }}">
          <a href="#">
            <i class="fa fa-book" aria-hidden="true"></i>
            <span>All Ledger</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" {!! $isLedgersActive ? 'style="display: block;"' : '' !!}>
            <li class="{{ request()->routeIs('ledgers.client') ? 'active' : '' }}"><a href="{{ route('ledgers.client') }}"><i class="fa fa-star-o"></i>Client Ledger</a></li>
            <li class="{{ request()->routeIs('ledgers.supplier') ? 'active' : '' }}"><a href="{{ route('ledgers.supplier') }}"><i class="fa fa-star-o"></i>Supplier Ledger</a></li>
            <li class="{{ request()->routeIs('ledgers.project') ? 'active' : '' }}"><a href="{{ route('ledgers.project') }}"><i class="fa fa-star-o"></i>Project Ledger</a></li>
            <li class="{{ request()->routeIs('ledgers.loan') ? 'active' : '' }}"><a href="{{ route('ledgers.loan') }}"><i class="fa fa-star-o"></i>Loan Ledger</a></li>
            <li class="{{ request()->routeIs('ledgers.office_expense') ? 'active' : '' }}"><a href="{{ route('ledgers.office_expense') }}"><i class="fa fa-star-o"></i>Office Expense Ledger</a></li>
            <li class="{{ request()->routeIs('ledgers.account_statement') ? 'active' : '' }}"><a href="{{ route('ledgers.account_statement') }}"><i class="fa fa-star-o"></i>Account Statement</a></li>
            <li class="{{ request()->routeIs('ledgers.receipts_payments') ? 'active' : '' }}"><a href="{{ route('ledgers.receipts_payments') }}"><i class="fa fa-star-o"></i>Receipts & Payments Report</a></li>     
          </ul>
        </li>

        @php
          $isTopLedgersActive = request()->is('top-ledgers*');
        @endphp
        <li class="treeview {{ $isTopLedgersActive ? 'active menu-open' : '' }}">
          <a href="#">
            <i class="fa fa-line-chart" aria-hidden="true"></i>
            <span> Top Ledger </span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" {!! $isTopLedgersActive ? 'style="display: block;"' : '' !!}>
            <li class="{{ request()->routeIs('top-ledgers.balance-statement') ? 'active' : '' }}"><a href="{{ route('top-ledgers.balance-statement') }}"><i class="fa fa-circle-o"></i> Revenue/Balance Statement</a></li>
            <li class="{{ request()->routeIs('top-ledgers.client-summary') ? 'active' : '' }}"><a href="{{ route('top-ledgers.client-summary') }}"><i class="fa fa-circle-o"></i> All Client Top Ledger</a></li>
            <li class="{{ request()->routeIs('top-ledgers.supplier-summary') ? 'active' : '' }}"><a href="{{ route('top-ledgers.supplier-summary') }}"><i class="fa fa-circle-o"></i> All Supplier Top Ledger</a></li>
            <li class="{{ request()->routeIs('top-ledgers.employee-ledger') ? 'active' : '' }}"><a href="{{ route('top-ledgers.employee-ledger') }}"><i class="fa fa-circle-o"></i> All Employee Top Ledger</a></li>
          </ul>
        </li>

        @php
          $isUserActive = request()->is('users*') || request()->is('approvals*') || request()->is('activity-log*');
        @endphp
        <li class="treeview {{ $isUserActive ? 'active menu-open' : '' }}">
          <a href="#">
            <i class="fa fa-user-circle" aria-hidden="true"></i>
            <span> User </span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" {!! $isUserActive ? 'style="display: block;"' : '' !!}>
            <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><a href="{{ route('users.index') }}"><i class="fa fa-circle-o"></i> User Add</a></li>
            <li class="{{ request()->routeIs('approvals.pending') ? 'active' : '' }}"><a href="{{ route('approvals.pending') }}"><i class="fa fa-circle-o"></i> Pending Approvals</a></li>
            <li class="{{ request()->routeIs('approvals.rejected') ? 'active' : '' }}"><a href="{{ route('approvals.rejected') }}"><i class="fa fa-circle-o"></i> Rejected Vouchers</a></li>
            <li class="{{ request()->routeIs('activity-log.*') ? 'active' : '' }}"><a href="{{ route('activity-log.index') }}"><i class="fa fa-circle-o"></i> Activity Log</a></li>
          </ul>
        </li>

      </ul>
    </section>
</aside>
