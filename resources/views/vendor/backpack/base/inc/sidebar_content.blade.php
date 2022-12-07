{{-- This file is used to store sidebar items, inside the Backpack admin panel --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('product') }}"><i class="nav-icon la la-box"></i> Products</a></li>

<!-- Member -->
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-id-card"></i> Members</a>
  <ul class="nav-dropdown-items">
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('member') }}"><i class="nav-icon la la-list"></i> List</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('member/create') }}"><i class="nav-icon la la-plus"></i> Add</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('activation-payments') }}"><i class="nav-icon la la-money-bill"></i> Payments</a></li>
  </ul>
</li>

<!-- Stock -->
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-archive"></i> Stock</a>
  <ul class="nav-dropdown-items">
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('stock') }}"><i class="nav-icon la la-list"></i> List</a></li>
    <li class="nav-item"><a class="nav-link" href="#"><i class="nav-icon la la-history"></i> Histories</a> </li>
  </ul>
</li>

<!-- Transactions -->
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-money-bill"></i> Transactions</a>
  <ul class="nav-dropdown-items">
    {{-- Transaction Normal --}}
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('transaction') }}"><i class="nav-icon la la-dollar"></i> <span>Normal</span></a></li>
    {{-- Tranasction Display --}}
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('transaction-display') }}"><i class="nav-icon la la-dollar"></i> <span>Display</span></a></li>
    {{-- Transaction Demokit --}}
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('transaction-demokit') }}"><i class="nav-icon la la-dollar"></i> <span>Demokit</span></a></li>
    {{-- Transaction Bebas Putus --}}
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('transaction-bebas-putus') }}"><i class="nav-icon la la-dollar"></i> <span>Bebas Putus</span></a></li>
    <li class= "nav-item"><a class="nav-link" href="{{ backpack_url('transaction-payment') }}"><i class="nav-icon la la-money-bill"></i> <span>Payments</span></a></li>
  </ul>
</li>

<li class="nav-item"><a class="nav-link" href="{{ backpack_url('level') }}"><i class="nav-icon la la-chart-line"></i> Levels</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('payment-method') }}"><i class="nav-icon la la-money-bill"></i> Payment Methods</a></li>

<!-- Histories -->
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-history"></i> Histories</a>
  <ul class="nav-dropdown-items">
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('bonus-history') }}"><i class="nav-icon la la-hand-holding-usd"></i> Bonus Histories</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('level-up-histories') }}"><i class="nav-icon la la-level-up-alt"></i> Level Up Histories</a></li>
  </ul>
</li>

<li class="nav-item"><a class="nav-link" href="{{ backpack_url('branch') }}"><i class="nav-icon la la-city"></i> Branches</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('customer') }}"><i class="nav-icon la la-users"></i> Customers</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('configuration') }}"><i class="nav-icon la la-tools"></i> Configurations</a></li>
<!-- Users, Roles, Permissions -->
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-users"></i> Authentication</a>
  <ul class="nav-dropdown-items">
      <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>Users</span></a></li>
      <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-id-badge"></i> <span>Roles</span></a></li>
      <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Permissions</span></a></li>
  </ul>
</li>